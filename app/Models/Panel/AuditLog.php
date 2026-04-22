<?php

namespace App\Models\Panel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'gm_user_id', 'action', 'target_type', 'target_id',
        'ip', 'user_agent', 'payload', 'result', 'success',
    ];

    protected $casts = [
        'payload' => 'array',
        'result'  => 'array',
        'success' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(GmUser::class, 'gm_user_id');
    }
}
