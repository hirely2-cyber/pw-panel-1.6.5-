<?php

namespace App\Models\Panel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GmRole extends Model
{
    protected $table = 'gm_roles';

    protected $fillable = ['name', 'color', 'is_super'];

    protected $casts = [
        'is_super' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(GmUser::class, 'gm_role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'gm_role_permissions',
            'gm_role_id',
            'permission_id',
        );
    }
}
