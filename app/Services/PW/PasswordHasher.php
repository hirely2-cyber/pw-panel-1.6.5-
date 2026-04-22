<?php

namespace App\Services\PW;

/**
 * Algoritma hash password untuk akun game PW (gauthd compatible).
 *
 * Mode yang didukung:
 *  - base64 : base64(md5(user.pass, raw=true))   ← PW default
 *  - md5    : md5(user.pass)
 *  - 0x.md5 : "0x" . md5(user.pass)
 *  - plain  : plain text
 */
final class PasswordHasher
{
    public static function hash(string $username, string $password, string $mode = 'base64'): string
    {
        $u = strtolower(trim($username));
        $p = $password;

        return match ($mode) {
            'base64' => base64_encode(md5($u . $p, true)),
            'md5'    => md5($u . $p),
            '0x.md5' => '0x' . md5($u . $p),
            'plain'  => $p,
            default  => throw new \InvalidArgumentException("Unknown PW hash mode: {$mode}"),
        };
    }

    public static function verify(string $username, string $password, string $storedHash, string $mode = 'base64'): bool
    {
        return hash_equals($storedHash, self::hash($username, $password, $mode));
    }
}
