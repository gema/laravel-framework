<?php

namespace GemaDigital\Framework\app\Macros\Filterable;

use Illuminate\Contracts\Database\Eloquent\Builder;

class FilterRelation implements FilterableContract
{
    public function __construct(
        public string $column,
        public string $relation,
    ) {}

    /**
     * Make a Filter Relation
     */
    public static function make(string $column, string $relation): FilterRelation
    {
        return new FilterRelation($column, $relation);
    }

    /**
     * Filter method
     */
    public function filter(Builder $query): void
    {
        $value = request()->{'filter_'.$this->column};
        $model = $query->getModel();

        // relation attributes
        $relation = $model->{$this->relation}();
        $table = $relation->getModel()->getTable();
        $key = $this->column;

        if (method_exists($relation, 'getLocalKeyName')) {
            $key = $relation->getLocalKeyName();
        }

        if ($value === null) {
            return;
        }

        $query->whereHas($this->relation, fn (Builder $q) => $q->{is_array($value) ? 'whereIn' : 'where'}("$table.$key", $value));
    }
}
