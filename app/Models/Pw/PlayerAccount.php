<?php

namespace App\Models\Pw;

use App\Services\PW\PasswordHasher;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Akun player PW — konek ke DB game (pw172.users).
 * Hash password harus format gauthd (base64/md5/0x.md5).
 */
class PlayerAccount extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $connection = 'pw_game';
    protected $table      = 'users';
    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [
        'name', 'passwd', 'passwd2', 'email', 'truename',
        'creatime', 'prompt', 'answer',
    ];

    protected $hidden = ['passwd', 'passwd2'];

    public function getAuthIdentifierName(): string
    {
        return 'ID';
    }

    public function getAuthPassword(): string
    {
        return (string) $this->passwd;
    }

    /**
     * Verifikasi password dengan algoritma gauthd aktif.
     */
    public function verifyPassword(string $plain, string $hashMode): bool
    {
        return PasswordHasher::verify($this->name, $plain, $this->passwd, $hashMode);
    }
}
