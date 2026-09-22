<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ArticleCommodity extends Pivot
{
    use HasUuids;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'article_commodities';

    protected $fillable = [
        'article_id',
        'commodity_id',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }
}