<?php

namespace GemaDigital\Macros\Searchable;

use Illuminate\Contracts\Database\Eloquent\Builder;

class SearchColumn implements SearchableContract
{
    public function __construct(
        private readonly string $column,
    ) {}

    /**
     * Make a Search Relation
     */
    public static function make(string $column): SearchColumn
    {
        return new SearchColumn($column);
    }

    /**
     * Search method
     */
    public function search(Builder $query, ?string $searchText): void
    {
        if (! $searchText) {
            return;
        }

        $query->orWhereLike($this->column, $searchText);
    }
}
