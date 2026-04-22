<?php

namespace App\Models\Panel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = ['name', 'group', 'label'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            GmRole::class,
            'gm_role_permissions',
            'permission_id',
            'gm_role_id',
        );
    }
}
