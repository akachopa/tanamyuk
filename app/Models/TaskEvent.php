<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskEvent extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'task_id',
        'user_id',
        'event_type',
        'event_at_client',
        'event_at_server',
        'notes',
        'client_mutation_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event_at_client' => 'datetime',
            'event_at_server' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}