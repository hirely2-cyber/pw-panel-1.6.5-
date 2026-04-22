<?php

namespace App\Services\PW;

use App\Services\PW\Roles\Stream;

/**
 * Cubi (cash) lookup per account — real-time dari gamedbd via RPC GetUser (opcode 3002).
 *
 * gamedbd menyimpan saldo live dalam memory dan mengembalikan field:
 *   cash       : balance aktual (yang pemain lihat di game)
 *   money      : yuan/silver
 *   cash_add   : total lifetime top-up
 *   cash_buy   : total beli dari player (trade)
 *   cash_sell  : total jual ke player (trade)
 *   cash_used  : total belanja di shop
 *
 *   balance = cash_add + cash_buy - cash_used - cash_sell
 *
 * SQL (usecashnow/usecashlog/pay) bukan sumber balance — cuma log order.
 */
class CashService
{
    /** Cents → display (divide 100). */
    public static function fmt(int $cents): float
    {
        return round($cents / 100, 2);
    }

    /**
     * Live Cubi balance via gamedbd.GetUser RPC. Null kalau gamedbd offline.
     */
    public function liveCash(int $userid): ?array
    {
        $srv    = Server::current();
        $dbPort = (int) $srv->db_port;

        // Build payload: writeInt32(-1) + writeInt32(userId) + writeInt32(0) + writeInt32(0)
        Stream::reset();
        Stream::writeInt32(-1);
        Stream::writeInt32($userid);
        Stream::writeInt32(0);
        Stream::writeInt32(0);
        Stream::pack(3002); // GetUser opcode

        try {
            $sock = Socket::fromCurrent();
            $payload = Socket::packInt($dbPort) . Stream::$writeData;
            $response = $sock->sendPacket(4, $payload, 2048);
        } catch (PwSocketException $e) {
            return null;
        }

        if ($response === '' || $response === false || $response === 'server:0') {
            return null;
        }

        Stream::putRead($response, 0);
        try {
            Stream::readCUint32(); // opcode
            Stream::readCUint32(); // length
            Stream::readInt32();   // local_sid
            $retcode = Stream::readInt32();
            if ($retcode !== 0) {
                return null;
            }
            $logicuid  = Stream::readInt32();
            $rolelist  = Stream::readInt32();
            $cash      = Stream::readInt32();
            $money     = Stream::readInt32();
            $cash_add  = Stream::readInt32();
            $cash_buy  = Stream::readInt32();
            $cash_sell = Stream::readInt32();
            $cash_used = Stream::readInt32();
        } catch (\Throwable $e) {
            return null;
        }

        return [
            'logicuid'  => $logicuid,
            'rolelist'  => $rolelist,
            'cash'      => $cash,
            'money'     => $money,
            'cash_add'  => $cash_add,
            'cash_buy'  => $cash_buy,
            'cash_sell' => $cash_sell,
            'cash_used' => $cash_used,
        ];
    }

    /**
     * Tambah Cubi ke account via DebugAddCash (opcode 0x209).
     * $amount = jumlah Cubi yang ditampilkan di game (bukan cents).
     * Secara internal dikirim × 100 karena gamedbd simpan dalam cents.
     * Returns true jika sukses.
     */
    public function addCubi(int $userid, int $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }

        $srv    = Server::current();
        $dbPort = (int) $srv->db_port;

        Stream::reset();
        Stream::writeInt32($userid);
        Stream::writeInt32($amount * 100); // cents
        Stream::pack(0x209); // DebugAddCash

        try {
            $sock    = Socket::fromCurrent();
            $payload = Socket::packInt($dbPort) . Stream::$writeData;
            $resp    = $sock->sendPacket(6, $payload);
        } catch (PwSocketException $e) {
            return false;
        }

        return $resp !== 'server:0' && $resp !== '' && $resp !== false;
    }

    /**
     * UI summary. Live balance dari gamedbd; fallback null kalau offline.
     */
    public function forAccount(int $userid): array
    {
        $live = $this->liveCash($userid);

        if ($live === null) {
            return [
                'online'    => false,
                'balance'   => null,
                'purchased' => null,
                'bought'    => null,
                'used'      => null,
                'sold'      => null,
                'money'     => null,
                'raw'       => null,
            ];
        }

        $balanceCents = $live['cash_add'] + $live['cash_buy'] - $live['cash_used'] - $live['cash_sell'];

        return [
            'online'    => true,
            'balance'   => self::fmt($balanceCents),
            'live_cash' => self::fmt($live['cash']),
            'purchased' => self::fmt($live['cash_add']),
            'bought'    => self::fmt($live['cash_buy']),
            'used'      => self::fmt($live['cash_used']),
            'sold'      => self::fmt($live['cash_sell']),
            'money'     => self::fmt($live['money']),
            'raw'       => $live,
        ];
    }
}
