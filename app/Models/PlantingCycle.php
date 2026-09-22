<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlantingCycle extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'garden_id',
        'commodity_id',
        'variety_id',
        'template_id',
        'template_version',
        'cultivation_method_id',
        'name',
        'start_date',
        'target_harvest_date',
        'quantity',
        'quantity_unit',
        'current_stage_id',
        'status',
        'cover_image_path',
        'notes',
        'server_version',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_harvest_date' => 'date',
            'quantity' => 'decimal:2',
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

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(CommodityVariety::class, 'variety_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CultivationTemplate::class, 'template_id');
    }

    public function cultivationMethod(): BelongsTo
    {
        return $this->belongsTo(CultivationMethod::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(CycleStage::class, 'current_stage_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(CycleStage::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function plantIssues(): HasMany
    {
        return $this->hasMany(PlantIssue::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }
}