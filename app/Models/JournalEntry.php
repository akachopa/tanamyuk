<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'garden_id',
        'planting_cycle_id',
        'task_id',
        'activity_type',
        'entry_date',
        'plant_condition',
        'duration_minutes',
        'notes',
        'client_mutation_id',
        'server_version',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'duration_minutes' => 'integer',
            'server_version' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function garden(): BelongsTo
    {
        return $this->belongsTo(Garden::class);
    }

    public function plantingCycle(): BelongsTo
    {
        return $this->belongsTo(PlantingCycle::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}