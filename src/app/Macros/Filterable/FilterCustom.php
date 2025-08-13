<?php

namespace GemaDigital\Macros\Filterable;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;

class FilterCustom implements FilterableContract
{
    /**
     * @param  array<string, mixed>  $dependencies
     */
    public function __construct(
        public string $column,
        public Closure $closure,
        public bool $callAlways,
        private readonly array $dependencies = [],
    ) {}

    /**
     * Make a Custom Filter
     */
    public static function make(string $column, Closure $closure, bool $callAlways = false): FilterCustom
    {
        return new FilterCustom($column, $closure, $callAlways);
    }

    /**
     * Make Custom Filter with other column dependancy
     *
     * @param  array<string, mixed>  $dependencies
     */
    public static function makeWith(string $column, array $dependencies, Closure $closure, bool $callAlways = false): FilterCustom
    {
        return new FilterCustom($column, $closure, $callAlways, $dependencies);
    }

    /**
     * Filter method
     */
    public function filter(Builder $query): void
    {
        $value = request()->{'filter_'.$this->column};

        // Preparing dependencies
        $dependencies = collect($this->dependencies)
            ->mapWithKeys(fn ($dependency) => ([$dependency => request()->{'filter_'.$dependency}]))->toArray();

        if ($value !== null || $this->callAlways) {
            ($this->closure)($query, $value, $dependencies);
        }
    }
}
