<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'ip_hash',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }
}