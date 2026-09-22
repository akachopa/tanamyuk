<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    use HasUuids;

    const CREATED_AT = null;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'summary',
        'content',
        'cover_image_path',
        'reading_minutes',
        'access_level',
        'version',
        'status',
        'published_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'reading_minutes' => 'integer',
            'published_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    public function commodities(): BelongsToMany
    {
        return $this->belongsToMany(Commodity::class, 'article_commodities')
            ->withPivot(['id'])
            ->using(ArticleCommodity::class);
    }
}