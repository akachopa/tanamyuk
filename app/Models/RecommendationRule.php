<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationRule extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'input_field',
        'operator',
        'comparison_value',
        'commodity_id',
        'category_id',
        'score_delta',
        'reason_template',
        'priority',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'score_delta' => 'integer',
            'priority' => 'integer',
        ];
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CommodityCategory::class, 'category_id');
    }
}