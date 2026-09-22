<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlantIssue extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'planting_cycle_id',
        'discovered_at',
        'symptom_category',
        'affected_part',
        'severity',
        'description',
        'action_taken',
        'status',
        'resolved_at',
        'client_mutation_id',
        'server_version',
    ];

    protected function casts(): array
    {
        return [
            'discovered_at' => 'datetime',
            'resolved_at' => 'datetime',
            'server_version' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plantingCycle(): BelongsTo
    {
        return $this->belongsTo(PlantingCycle::class);
    }
}