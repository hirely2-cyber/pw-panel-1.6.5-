<?php

namespace App\Models\Panel;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class GmUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'mysql';
    protected $table = 'gm_users';

    protected $fillable = [
        'username', 'email', 'display_name', 'password',
        'gm_role_id', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'password'                 => 'hashed',
            'is_active'                => 'boolean',
            'last_login_at'            => 'datetime',
            'two_factor_confirmed_at'  => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(GmRole::class, 'gm_role_id');
    }

    public function can(mixed $abilities, mixed $arguments = []): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $role = $this->role;
        if (! $role) {
            return false;
        }

        if ($role->is_super) {
            return true;
        }

        $abilities = is_array($abilities) ? $abilities : [$abilities];
        $granted   = $role->permissions()->pluck('name')->all();

        foreach ($abilities as $ability) {
            if (in_array($ability, $granted, true)) {
                return true;
            }
        }
        return false;
    }
}
