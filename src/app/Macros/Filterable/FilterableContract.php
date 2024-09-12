<?php

namespace GemaDigital\Framework\app\Macros\Filterable;

use Illuminate\Contracts\Database\Eloquent\Builder;

interface FilterableContract
{
    public function filter(Builder $query): void;
}
