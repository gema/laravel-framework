<?php

namespace GemaDigital\Macros\Filterable;

use Illuminate\Contracts\Database\Eloquent\Builder;

class FilterColumn implements FilterableContract
{
    public const STRING = 'string';

    public const INTEGER = 'int';

    public const BOOLEAN = 'bool';

    public function __construct(
        public string $column,
        public ?string $type,
    ) {}

    /**
     * Make a Filter Relation
     */
    public static function make(string $column, ?string $type = null): FilterColumn
    {
        return new FilterColumn($column, $type);
    }

    /**
     * Filter method
     */
    public function filter(Builder $query): void
    {
        $value = request()->{'filter_'.$this->column};

        if ($value === null) {
            return;
        }

        if (is_array($value)) {
            if ($this->type) {
                $value = array_map(fn ($v) => settype($v, $this->type), $value);
            }

            $query->whereIn($this->column, $value);
        } else {
            if ($this->type) {
                settype($value, $this->type);
            }

            $query->where($this->column, $value);
        }
    }
}
