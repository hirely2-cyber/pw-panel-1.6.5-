<?php

namespace App\Services\PW;

use Illuminate\Support\Facades\Log;

/**
 * Broadcast chat message ke dalam game (in-game notification).
 *
 * Pakai opcode 3 (forward ke gdeliveryd / gprovider) dengan payload:
 *   packInt(gprovider_port) . streamData
 *
 * streamData layout (sama persis seperti iweb serverModel::sendChatMessage):
 *   writeByte(channel)        1 byte  — 0=Local 1=World 3=Faction 9=System ...
 *   writeByte(0)              1 byte  — emote (0)
 *   writeInt32(0,big)         4 byte  — sender roleid (0 = system)
 *   writeString(msg)          cuint(len*2) + utf16le(msg)
 *   writeOctets("")           cuint(0)
 *   pack(0x78)                prepend cuint(opcode) + cuint(body_len)
 *
 * Channel id (sama dengan iweb template):
 *   0=Local 1=World 2=Party 3=Faction 7=Trade 9=System
 *   11=Solo 12=Horn 13=ATK 14=Dynasty 15=Crosserver
 */
final class ChatBroadcaster
{
    public const CH_LOCAL      = 0;
    public const CH_WORLD      = 1;
    public const CH_PARTY      = 2;
    public const CH_FACTION    = 3;
    public const CH_TRADE      = 7;
    public const CH_SYSTEM     = 9;
    public const CH_SOLO       = 11;
    public const CH_HORN       = 12;
    public const CH_ATK        = 13;
    public const CH_DYNASTY    = 14;
    public const CH_CROSS      = 15;

    public function __construct(private readonly Socket $socket) {}

    public static function forCurrent(): self
    {
        return new self(Socket::fromCurrent());
    }

    /**
     * Broadcast a single chat message.
     *
     * @return bool true on success
     */
    public function send(string $message, int $channel = self::CH_SYSTEM): bool
    {
        if (trim($message) === '') return false;

        $port = Server::current()->gprovider_port ?? 29300;

        // Body (sebelum di-pack dengan opcode chat 0x78)
        $body  = pack('C', $channel & 0xFF);    // writeByte(channel)
        $body .= pack('C', 0);                  // writeByte(0) emote
        $body .= strrev(pack('N', 0));          // writeInt32(0, big=true) — iweb default
        // writeString: utf16le + cuint(byte_len) prefix
        $utf16 = @iconv('UTF-8', 'UTF-16LE', $message);
        if ($utf16 === false) $utf16 = '';
        $body .= self::cuint(strlen($utf16)) . $utf16;
        // writeOctets("") — cuint(0) + empty
        $body .= self::cuint(0);

        // pack(0x78): prepend cuint(opcode) + cuint(body_len)
        $packet = self::cuint(0x78) . self::cuint(strlen($body)) . $body;

        // Forward via opcode 3 to gprovider_port
        try {
            $resp = $this->socket->sendPacket(3, Socket::packInt($port) . $packet);
            return $resp !== 'server:0' && $resp !== '';
        } catch (PwSocketException $e) {
            Log::warning('ChatBroadcaster::send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * cuint encoding (same as iweb stream::cuint).
     */
    private static function cuint(int $v): string
    {
        if ($v <= 127)         return pack('C', $v);
        if ($v < 16384)        return pack('n', $v | 0x8000);
        if ($v < 536870912)    return pack('N', $v | 0xC0000000);
        return pack('c', -32) . pack('N', $v);
    }
}
