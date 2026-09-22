<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feature extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'code',
        'name',
        'description',
        'value_type',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features')
            ->withPivot(['id', 'value_boolean', 'value_number', 'value_text'])
            ->using(PlanFeature::class);
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }
}