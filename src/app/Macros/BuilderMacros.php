<?php

namespace App\Macros;

use App\Macros\Filterable\FilterColumn;
use App\Macros\Searchable\SearchColumn;
use App\Macros\Filterable\FilterRelation;
use App\Macros\Searchable\SearchRelation;
use Illuminate\Database\Eloquent\Builder;
use GemaDigital\Framework\app\Models\Model;
use App\Macros\Filterable\FilterableContract;
use App\Macros\Searchable\SearchableContract;

class BuilderMacros
{
    public static function register(): void
    {
        Builder::macro('whereLike', function (string $column, string $search): Builder {
            /** @var Builder<Model> */
            $builder = $this;

            return $builder->where($column, 'LIKE', "%{$search}%");
        });

        Builder::macro('orWhereLike', function (string $column, string $search): Builder {
            /** @var Builder<Model> */
            $builder = $this;

            return $builder->orWhere($column, 'LIKE', "%{$search}%");
        });

        Builder::macro('filterable', function (array $attributes) {
            /** @var Builder<Model> */
            $builder = $this;

            if (count($attributes) === 0) {
                return $builder;
            }

            // Map simple search
            $attributes = collect($attributes)->map(function ($attribute) {
                if (is_string($attribute)) {
                    if (str_contains($attribute, '.')) {
                        return FilterRelation::make(...explode('.', $attribute));
                    } else {
                        return FilterColumn::make($attribute);
                    }
                }

                return $attribute;
            });

            $builder->where(function (Builder $query) use ($attributes) {
                collect($attributes)
                    ->each(function (FilterableContract $attribute) use ($query) {
                        $attribute->filter($query);
                    });
            });

            return $builder;
        });

        Builder::macro('searchable', function (array $attributes) {
            $request = request();

            /** @var Builder<Model> */
            $builder = $this;

            if (! $request->has('query') || count($attributes) === 0) {
                return $builder;
            }

            // Map simple search
            $attributes = collect($attributes)->map(function ($attribute) {
                if (is_string($attribute)) {
                    if (str_contains($attribute, '.')) {
                        return SearchRelation::make(...explode('.', $attribute));
                    } else {
                        return SearchColumn::make($attribute);
                    }
                }

                return $attribute;
            });

            $search = $request->get('query');
            $builder->where(function ($query) use ($search, $attributes) {
                collect($attributes)
                    ->each(function (SearchableContract $attribute) use ($query, $search) {
                        $attribute->search($query, $search);
                    });
            });

            return $builder;
        });

        Builder::macro('orderable', function (?string $orderBy = null, string $orderDir = 'desc') {
            $request = request();

            /** @var Builder<Model> */
            $builder = $this;

            // Set the defaults
            if (! $request->has('order_by') && $orderBy) {
                $request->merge(['order_by' => $orderBy]);

                if (! $request->has('order_dir') && $orderDir) {
                    $request->merge(['order_dir' => $orderDir]);
                }
            }

            // early return
            if (empty($request->order_by)) {
                return $builder;
            }

            $orderable = array_merge(
                $builder->getModel()->getFillable(),
                $builder->getModel()->orderable ?? [],
            );
            // add common order by
            $orderable[] = 'id';
            $orderable[] = 'created_at';
            $orderable[] = 'updated_at';
            $orderable[] = 'deleted_at';

            $request->validate([
                'order_by' => 'nullable|string|in:'.implode(',', $orderable),
                'order_dir' => 'nullable|string|in:desc,asc',
            ]);

            // apply order
            if (str_contains((string) $request->order_by, '.')) {
                [$relation, $column] = explode('.', (string) $request->order_by);

                $relationModel = $builder->getModel()->{$relation}();
                $relatedModel = $relationModel->getRelated();

                $relationForeignKeyName = $relationModel->getForeignKeyName();
                $relatedTable = $relatedModel->getTable();
                $relatedKeyName = $relatedModel->getKeyName();

                $builder
                    ->join("$relatedTable as {$relatedTable}_order", "{$relatedTable}_order.$relatedKeyName", '=', $relationForeignKeyName)
                    ->orderBy("{$relatedTable}_order.$column", $request->order_dir ?? 'asc');
            } else {
                return $builder->orderBy($request->order_by, $request->order_dir ?? 'asc');
            }

            return $builder;
        });

        Builder::macro('filterableHaving', function (string $attribute) {
            /** @var Builder<Model> */
            $builder = $this;

            $value = request()->{'filter_'.$attribute};

            if ($value !== null) {
                $builder->having($attribute, $value);
            }

            return $builder;
        });
    }
}
