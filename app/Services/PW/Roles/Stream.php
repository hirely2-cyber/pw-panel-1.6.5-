<?php

namespace App\Services\PW\Roles;

/**
 * Binary stream reader/writer — ported dari iweb `system\libs\stream`.
 *
 * Layout: static class dengan buffer global + pointer. Pattern sengaja
 * dipertahankan sama dengan iweb supaya GRoleReader / struct lama
 * tetap kompatibel. Thread-safe? tidak — cukup untuk 1 request HTTP.
 */
class Stream
{
    public static string $readData = "";
    public static string $readData_copy = "";
    public static string $writeData = "";
    public static string $writeData_copy = "";
    public static int $p = 0;
    public static int $p_copy = 0;
    public static int $pack = 0;
    public static int $length = 8;
    public static int $error = 0;

    public static function reset(): void
    {
        self::$readData = "";
        self::$writeData = "";
        self::$p = 0;
        self::$length = 8;
        self::$error = 0;
    }

    public static function putRead(string $data, int $p = 0): void
    {
        self::$readData_copy = self::$readData;
        self::$readData = $data;
        self::$p_copy = self::$p;
        self::$p = $p;
        self::$length = strlen($data);
    }

    public static function putWrite(string $data): void
    {
        self::$writeData_copy = self::$writeData;
        self::$writeData = $data;
    }

    public static function cuint(int $data): string
    {
        if ($data <= 127) return pack("C", $data);
        if ($data < 16384) return pack("n", $data | 32768);
        if ($data < 536870912) return pack("N", $data | 3221225472);
        return pack("c", -32) . pack("N", $data);
    }

    public static function pack(int $id): void
    {
        self::$writeData = self::cuint($id) . self::cuint(strlen(self::$writeData)) . self::$writeData;
    }

    public static function getValue(string $type, bool $octetToInt = false): array
    {
        $data = ['type' => $type, 'value' => null];
        switch ($type) {
            case "int16":       $data['value'] = self::readInt16(); break;
            case "int32":       $data['value'] = self::readInt32(); break;
            case "int16sm":     $data['value'] = self::readInt16(false); break;
            case "int32sm":     $data['value'] = self::readInt32(false); break;
            case "int16rev":    $data['value'] = self::readInt16Rev(); break;
            case "int32rev":    $data['value'] = self::readInt32Rev(); break;

            case "cuint":
                $data['value'] = self::readCUint32();
                GRoleReader::$cycle = $data['value'] > 0 ? $data['value'] : -1;
                break;
            case "cuint-nc":
                $data['value'] = self::readCUint32();
                break;
            case "cuint16":
                $data['value'] = self::readInt16();
                GRoleReader::$cycle = $data['value'] > 0 ? $data['value'] : -1;
                break;
            case "cuint32":
                $data['value'] = self::readInt32();
                GRoleReader::$cycle = $data['value'] > 0 ? $data['value'] : -1;
                break;
            case "cuint16sm":
                $data['value'] = self::readInt16(false);
                GRoleReader::$cycle = $data['value'] > 0 ? $data['value'] : -1;
                break;
            case "cuint32sm":
                $data['value'] = self::readInt32(false);
                GRoleReader::$cycle = $data['value'] > 0 ? $data['value'] : -1;
                break;
            case "cbyte":
                $data['value'] = self::readByte();
                GRoleReader::$cycle = $data['value'] > 0 ? $data['value'] : -1;
                break;

            case "float":      $data['value'] = self::readSingle(); break;
            case "float-sm":   $data['value'] = self::readSingle(false); break;
            case "byte":       $data['value'] = self::readByte(); break;
            case "octets":     $data['value'] = self::readOctets($octetToInt); break;
            case "string":     $data['value'] = self::readString(); break;
            case "color":      $data['value'] = self::readColor(); break;
            case "timestamp":
                $time = self::readInt32();
                $sec = $time - time();
                $data['value'] = $sec <= 0 ? 0 : $sec;
                break;
            default:           $data['value'] = ""; break;
        }
        return $data;
    }

    // ---- Read ----

    public static function readInt16(bool $big = true): int
    {
        if (self::$length >= self::$p + 2) {
            $d = substr(self::$readData, self::$p, 2);
            if (strlen($d) == 2) {
                $r = $big ? unpack("n", $d) : unpack("v", $d);
                self::$p += 2;
                return $r[1];
            } else self::$error++;
        }
        return 0;
    }

    public static function readInt16Rev(): int
    {
        if (self::$length >= self::$p + 2) {
            $d = substr(self::$readData, self::$p, 2);
            if (strlen($d) == 2) {
                $r = unpack("n", strrev($d));
                self::$p += 2;
                return $r[1];
            } else self::$error++;
        }
        return 0;
    }

    public static function readInt32(bool $big = true): int
    {
        if (self::$length >= self::$p + 4) {
            $d = substr(self::$readData, self::$p, 4);
            if (strlen($d) == 4) {
                $r = $big ? unpack("i", strrev($d)) : unpack("i", $d);
                self::$p += 4;
                return $r[1];
            } else self::$error++;
        }
        return 0;
    }

    public static function readInt32Rev(): int
    {
        if (self::$length >= self::$p + 4) {
            $d = substr(self::$readData, self::$p, 4);
            if (strlen($d) == 4) {
                $r = unpack("N", strrev($d));
                self::$p += 4;
                return $r[1];
            } else self::$error++;
        }
        return 0;
    }

    public static function readByte(): int
    {
        if (self::$length >= self::$p + 1) {
            $d = substr(self::$readData, self::$p, 1);
            if (strlen($d) == 1) {
                $r = unpack("C", $d);
                self::$p++;
                return $r[1];
            } else self::$error++;
        }
        return 0;
    }

    public static function readOctets(bool $toInt = false): string|int
    {
        $size = self::readCUint32();
        $data = substr(self::$readData, self::$p, $size);
        if (strlen($data) == $size) {
            $result = $toInt ? bin2hex($data) : $data;
            self::$p += $size;
            return $result;
        }
        self::$error++;
        return "";
    }

    public static function readCUint32(): int
    {
        $byte = self::readByte();
        self::$p -= 1;
        switch ($byte & 224) {
            case 224:
                self::readByte();
                return self::readInt32();
            case 192:
                return self::readInt32() & 1073741823;
            case 128:
            case 160:
                return self::readInt16() & 32767;
        }
        return self::readByte();
    }

    public static function readString(): string
    {
        $size = self::readCUint32();
        $r = substr(self::$readData, self::$p, $size);
        if (strlen($r) == $size) {
            self::$p += $size;
            $r = @iconv("UTF-16LE", "UTF-8//IGNORE", $r);
            return $r === false ? "" : $r;
        }
        self::$error++;
        return "";
    }

    public static function readSingle(bool $big = true): float
    {
        if (self::$length >= self::$p + 4) {
            $d = substr(self::$readData, self::$p, 4);
            if (strlen($d) == 4) {
                $r = $big ? unpack("f", strrev($d)) : unpack("f", $d);
                self::$p += 4;
                return $r[1];
            } else self::$error++;
        }
        return 0.0;
    }

    public static function readColor(): string
    {
        return dechex(self::readInt32Rev());
    }

    // ---- Write ----

    public static function writeInt16(int $data, bool $big = true): void
    {
        self::$writeData .= $big ? pack("n", $data) : pack("v", $data);
    }

    public static function writeInt32(int $data, bool $big = true): void
    {
        self::$writeData .= $big ? pack("N", $data) : pack("V", $data);
    }

    public static function writeByte(int $data): void
    {
        self::$writeData .= pack("C", $data);
    }

    public static function writeInt16Rev(int $data): void
    {
        self::$writeData .= strrev(pack('n', $data));
    }

    public static function writeInt32Rev(int $data): void
    {
        self::$writeData .= strrev(pack('N', $data));
    }

    public static function writeCUint32(int $data): void
    {
        self::$writeData .= self::cuint($data);
    }

    public static function writeSingle(float $data, bool $big = true): void
    {
        self::$writeData .= $big ? strrev(pack('f', $data)) : pack('f', $data);
    }

    public static function writeOctets(string $data, bool $toData = false): void
    {
        if ($toData) {
            $pack = (string) @pack('H*', $data);
            self::$writeData .= self::cuint(strlen($pack)) . $pack;
        } else {
            self::$writeData .= self::cuint(strlen($data)) . $data;
        }
    }

    public static function writeString(string $data): void
    {
        $result = @iconv('UTF-8', 'UTF-16LE', $data);
        if ($result === false) $result = '';
        self::$writeData .= self::cuint(strlen($result)) . $result;
    }

    public static function writeColor(string $data): void
    {
        self::writeInt32Rev((int) hexdec($data));
    }

    /**
     * Write a typed value — mirror of iweb stream::putValue().
     */
    public static function putValue(mixed $data, string $type, bool $intToOctet = false): void
    {
        switch ($type) {
            case 'int16':      self::writeInt16((int) $data); break;
            case 'int32':      self::writeInt32((int) $data); break;
            case 'int16sm':    self::writeInt16((int) $data, false); break;
            case 'int32sm':    self::writeInt32((int) $data, false); break;
            case 'int16rev':   self::writeInt16Rev((int) $data); break;
            case 'int32rev':   self::writeInt32Rev((int) $data); break;
            case 'cuint':
                GRoleReader::$cycle = ((int) $data > 0) ? (int) $data : -1;
                self::writeCUint32((int) $data);
                break;
            case 'cuint-nc':   self::writeCUint32((int) $data); break;
            case 'cuint16':
            case 'cuint16sm':
                GRoleReader::$cycle = ((int) $data > 0) ? (int) $data : -1;
                self::writeInt16((int) $data, false);
                break;
            case 'cuint32':
            case 'cuint32sm':
                GRoleReader::$cycle = ((int) $data > 0) ? (int) $data : -1;
                self::writeInt32((int) $data, false);
                break;
            case 'cbyte':
                GRoleReader::$cycle = ((int) $data > 0) ? (int) $data : -1;
                self::writeByte((int) $data);
                break;
            case 'float':      self::writeSingle((float) $data); break;
            case 'float-sm':   self::writeSingle((float) $data, false); break;
            case 'byte':       self::writeByte((int) $data); break;
            case 'octets':     self::writeOctets((string) $data, $intToOctet); break;
            case 'string':     self::writeString((string) $data); break;
            case 'color':      self::writeColor((string) $data); break;
            case 'timestamp':
                $second = time() + (int) $data;
                self::writeInt32($second === time() ? 0 : $second);
                break;
        }
    }
}
