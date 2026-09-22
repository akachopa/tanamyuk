<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class PivotSync
{
    /**
     * Replace related rows on a UUID pivot table.
     *
     * @param  array<int, string>  $ids
     */
    public static function sync(BelongsToMany $relation, array $ids): void
    {
        $relation->detach();

        foreach (array_unique($ids) as $id) {
            self::attach($relation, $id);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function attach(BelongsToMany $relation, string $id, array $attributes = []): void
    {
        $relation->attach($id, ['id' => (string) Str::uuid(), ...$attributes]);
    }
}
