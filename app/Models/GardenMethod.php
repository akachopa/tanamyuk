<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GardenMethod extends Pivot
{
    use HasUuids;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'garden_methods';

    protected $fillable = [
        'garden_id',
        'cultivation_method_id',
    ];

    public function garden(): BelongsTo
    {
        return $this->belongsTo(Garden::class);
    }

    public function cultivationMethod(): BelongsTo
    {
        return $this->belongsTo(CultivationMethod::class);
    }
}