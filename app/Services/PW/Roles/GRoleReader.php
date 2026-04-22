<?php

namespace App\Services\PW\Roles;

use App\Services\PW\Roles\Structs\Character174;
use App\Services\PW\Socket;

/**
 * GRole binary codec — ported dari iweb `system\libs\GRole`.
 * Hanya jalur read (decode): readCharacter / readData / readItemData.
 *
 * Character struct tiap versi ada di `Structs\CharacterXXX`. Class ini
 * menerima nama class struct via setStruct() sebelum readCharacter().
 */
class GRoleReader
{
    public static int|false $cycle = false;

    /** FQCN dari class struct (mis. Character174) */
    public static string $structClass = Character174::class;

    public static function setStruct(string $class): void
    {
        self::$structClass = $class;
    }

    /**
     * Read role binary dari socket gamedbd (opcode forward = 4).
     * Returns ['role' => [...]] atau array kosong jika gagal.
     */
    public static function readCharacter(Socket $sock, int $dbPort, int $roleId, bool $dataToOctet = false): array
    {
        $structClass = self::$structClass;

        // Build request packet: writeInt32(-1) + writeInt32(id) + pack(getRole)
        Stream::reset();
        Stream::writeInt32(-1);
        Stream::writeInt32($roleId);
        Stream::pack($structClass::$pack['getRole']);

        $payload = Socket::packInt($dbPort) . Stream::$writeData;
        // opcode 4 = forward to gdeliveryd (which routes to gamedbd)
        $response = $sock->sendPacket(4, $payload, 2048 * 100);

        if ($response === 'server:0' || $response === '' || $response === false) {
            return ['role' => []];
        }

        Stream::putRead($response, 0);
        Stream::$pack = Stream::readCUint32();
        Stream::$length = Stream::readCUint32() + Stream::$p;
        Stream::readInt32(); // -1
        Stream::readInt32(); // id

        $data = ['role' => []];
        foreach ($structClass::$structure['role'] as $key => $inner) {
            $data['role'][$key] = self::readData($inner, $dataToOctet);
        }
        return $data;
    }

    /**
     * Recursive struct walker.
     */    /**
     * Recursively encode parsed role data back to binary — mirror of iweb GRole::writeData().
     */
    public static function writeData(array $data, bool $octetToData = true): void
    {
        $structClass = self::$structClass;
        $addons      = $structClass::$addons;

        foreach ($data as $key => $value) {
            if (!is_array($value)) continue;

            if (isset($value['value']) && isset($value['type'])) {
                Stream::putValue($value['value'], $value['type'], $octetToData);
            } else {
                if (isset($addons[$key])) {
                    // Encode addon subtree into a fresh buffer, then wrap as octets
                    $savedMain         = Stream::$writeData;
                    Stream::$writeData = '';
                    self::writeData($value, $octetToData);
                    $addonBin          = Stream::$writeData;
                    Stream::$writeData = $savedMain;
                    Stream::writeOctets($addonBin, false);
                } else {
                    self::writeData($value, $octetToData);
                }
            }
        }
    }

    /**
     * Send putRole (0x1F42) to gamedbd via opcode 3.
     * Returns true on success, false when server returns "server:0".
     *
     * $octetToData = false  → octets fields are raw binary strings (default after fetch())
     * $octetToData = true   → octets fields are hex strings, convert back before sending
     */
    public static function writeCharacter(Socket $sock, int $dbPort, int $roleId, array $role, bool $octetToData = false): bool
    {
        $structClass = self::$structClass;

        Stream::reset();
        Stream::writeInt32(-1);
        Stream::writeInt32($roleId);
        Stream::writeByte(1);
        self::writeData($role, $octetToData);
        Stream::pack($structClass::$pack['putRole']);

        $response = $sock->sendPacket(3, Socket::packInt($dbPort) . Stream::$writeData);
        return $response !== 'server:0';
    }
    public static function readData(array $structure, bool $octetToInt = false): array
    {
        $structClass = self::$structClass;
        $addons = $structClass::$addons;
        $data = [];

        foreach ($structure as $key => $value) {
            if (is_array($value)) {
                if (self::$cycle !== false) {
                    if (self::$cycle > 0) {
                        $n = self::$cycle;
                        self::$cycle = false;
                        for ($i = 0; $i < $n; $i++) {
                            $data[$key][$i] = self::readData($value, $octetToInt);
                        }
                    } else {
                        self::$cycle = false;
                    }
                } else {
                    $data[$key] = self::readData($value);
                }
            } else {
                if (isset($addons[$key])) {
                    $getOctet = Stream::getValue($value);
                    Stream::putRead($getOctet['value'], 0);
                    foreach ($addons[$key] as $addonKey => $addonVal) {
                        if (is_array($addonVal)) {
                            if (self::$cycle !== false) {
                                if (self::$cycle > 0) {
                                    $n = self::$cycle;
                                    self::$cycle = false;
                                    for ($i = 0; $i < $n; $i++) {
                                        $data[$key][$addonKey][$i] = self::readData($addonVal);
                                    }
                                } else {
                                    self::$cycle = false;
                                }
                            } else {
                                $data[$key][$addonKey] = self::readData($addonVal);
                            }
                        } else {
                            $data[$key][$addonKey] = Stream::getValue($addonVal, $octetToInt);
                        }
                    }
                    // restore previous buffer
                    Stream::putRead(Stream::$readData_copy, Stream::$p_copy);
                } else {
                    $data[$key] = Stream::getValue($value, $octetToInt);
                }
            }
        }
        return $data;
    }
}
