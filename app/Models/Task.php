<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'planting_cycle_id',
        'cycle_stage_id',
        'source_template_task_id',
        'task_type',
        'title',
        'instruction',
        'scheduled_at',
        'due_at',
        'status',
        'completed_at',
        'postponed_to',
        'is_manual',
        'server_version',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'postponed_to' => 'datetime',
            'is_manual' => 'boolean',
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

    public function cycleStage(): BelongsTo
    {
        return $this->belongsTo(CycleStage::class);
    }

    public function sourceTemplateTask(): BelongsTo
    {
        return $this->belongsTo(TemplateTask::class, 'source_template_task_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TaskEvent::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }
}