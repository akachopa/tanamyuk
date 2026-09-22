<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommodityCategory extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function commodities(): HasMany
    {
        return $this->hasMany(Commodity::class, 'category_id');
    }
}