<?php

namespace GemaDigital\Macros\Searchable;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Builder;

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
    public function search(BuilderContract $query, ?string $searchText): void
    {
        if (! $searchText) {
            return;
        }

        $method = $this->orCondition ? 'orWhereHas' : 'whereHas';

        // Use a normal closure so we don't return the (possibly wrongly typed) result of whereLike.
        $query->{$method}($this->relation, function (Builder $q) use ($searchText): void {
            $q->whereLike($this->column, $searchText);
        });
    }
}
