<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Garden extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'location_type',
        'area_m2',
        'length_m',
        'width_m',
        'sunlight_hours',
        'shade_level',
        'water_source',
        'drainage_level',
        'notes',
        'cover_image_path',
        'status',
        'client_created_at',
        'server_version',
    ];

    protected function casts(): array
    {
        return [
            'area_m2' => 'decimal:2',
            'length_m' => 'decimal:2',
            'width_m' => 'decimal:2',
            'sunlight_hours' => 'decimal:2',
            'client_created_at' => 'datetime',
            'server_version' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cultivationMethods(): BelongsToMany
    {
        return $this->belongsToMany(CultivationMethod::class, 'garden_methods')
            ->withPivot(['id'])
            ->using(GardenMethod::class);
    }

    public function plantingCycles(): HasMany
    {
        return $this->hasMany(PlantingCycle::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}