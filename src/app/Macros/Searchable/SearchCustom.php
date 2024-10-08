<?php

namespace GemaDigital\Macros\Searchable;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;

class SearchCustom implements SearchableContract
{
    public function __construct(
        private readonly Closure $closure,
    ) {}

    /**
     * Make a Search Relation
     */
    public static function make(Closure $closure): SearchCustom
    {
        return new SearchCustom($closure);
    }

    /**
     * Search method
     */
    public function search(Builder $query, ?string $searchText): void
    {
        if (! $searchText) {
            return;
        }

        ($this->closure)($query, $searchText);
    }
}
