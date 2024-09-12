<?php

namespace GemaDigital\Framework\app\Macros\Searchable;

use Illuminate\Contracts\Database\Eloquent\Builder;

class SearchRelation implements SearchableContract
{
    public function __construct(
        public string $relation,
        public string $column,
        public bool $orCondition = true,
    ) {}

    /**
     * Make a Search Relation
     */
    public static function make(string $relation, string $column, bool $orCondition = true): SearchRelation
    {
        return new SearchRelation($relation, $column, $orCondition);
    }

    /**
     * Search method
     */
    public function search(Builder $query, ?string $searchText): void
    {
        if (! $searchText) {
            return;
        }

        $method = $this->orCondition ? 'orWhereHas' : 'whereHas';

        $query->{$method}($this->relation, fn (Builder $q): Builder => $q->whereLike($this->column, $searchText));
    }
}
