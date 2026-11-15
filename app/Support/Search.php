<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class Search
{
    public static function apply(Builder $query, array $columns, string $term): Builder
    {
        // '!' sebagai karakter escape karena backslash diperlakukan berbeda oleh MySQL dan SQLite.
        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';

        return $query->where(function (Builder $w) use ($columns, $pattern) {
            foreach ($columns as $column) {
                $w->orWhereRaw($w->getQuery()->getGrammar()->wrap($column)." like ? escape '!'", [$pattern]);
            }
        });
    }
}
