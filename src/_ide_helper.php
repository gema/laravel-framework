<?php

/**
 * IDE Helper for GemaDigital Laravel Framework Macros
 * 
 * This file provides IntelliSense support for all custom macros
 * registered in the GemaDigital framework.
 */

namespace Illuminate\Contracts\Routing {

    interface ResponseFactory
    {
        /**
         * Return a custom API response.
         *
         * @param mixed|null $data
         * @param int $code
         * @param int $status
         * @param mixed|null $errors
         * @param mixed|null $exception
         * @return \Illuminate\Http\Response
         */
        public function api($data = null, $code = 0, $status = 200, $errors = null, $exception = null);

        /**
         * Return a custom API response with raw data.
         *
         * @param mixed|null $raw
         * @param int $code
         * @param int $status
         * @param mixed|null $errors
         * @return \Illuminate\Http\Response
         */
        public function apiRaw($raw = null, $code = 0, $status = 200, $errors = null);

        /**
         * Return a custom API error response.
         *
         * @param mixed|null $errors
         * @param int $code
         * @param int $status
         * @return \Illuminate\Http\Response
         */
        public function apiError($errors = null, $code = -1, $status = 400);

        /**
         * Return a custom API status response.
         *
         * @param bool $status
         * @param int $success
         * @param int $fail
         * @return \Illuminate\Http\Response
         */
        public function apiStatus(bool $status, $success = 200, $fail = 400);

        /**
         * Return a custom API response with pagination.
         *
         * @param mixed|null $data
         * @param \Illuminate\Pagination\LengthAwarePaginator|null $pagination
         * @param int $code
         * @param int $status
         * @param mixed|null $errors
         * @param mixed|null $exception
         * @return \Illuminate\Http\Response
         */
        public function apiPagination($data = null, ?\Illuminate\Pagination\LengthAwarePaginator $pagination = null, $code = 0, $status = 200, $errors = null, $exception = null);
    }
}

namespace Illuminate\Http {

    class Request
    {
        /**
         * Get the current access token for the authenticated user.
         *
         * @return \Laravel\Sanctum\PersonalAccessToken|\Laravel\Sanctum\TransientToken|null
         */
        public function currentAccessToken() {}
    }
}

namespace Illuminate\Database\Eloquent {

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     */
    class Builder
    {
        /**
         * Add a "where like" clause to the query.
         *
         * @param string $column
         * @param string $search
         * @return \Illuminate\Database\Eloquent\Builder<TModel>
         */
        public function whereLike(string $column, string $search) {}

        /**
         * Add an "or where like" clause to the query.
         *
         * @param string $column
         * @param string $search
         * @return \Illuminate\Database\Eloquent\Builder<TModel>
         */
        public function orWhereLike(string $column, string $search) {}

        /**
         * Apply filterable attributes to the query.
         *
         * @param array $attributes
         * @return \Illuminate\Database\Eloquent\Builder<TModel>
         */
        public function filterable(array $attributes) {}

        /**
         * Apply searchable attributes to the query.
         *
         * @param array $attributes
         * @return \Illuminate\Database\Eloquent\Builder<TModel>
         */
        public function searchable(array $attributes) {}

        /**
         * Apply orderable attributes to the query.
         *
         * @param string|null $orderBy
         * @param string $orderDir
         * @return \Illuminate\Database\Eloquent\Builder<TModel>
         */
        public function orderable(?string $orderBy = null, string $orderDir = 'desc') {}

        /**
         * Apply filterable having clause to the query.
         *
         * @param string $attribute
         * @return \Illuminate\Database\Eloquent\Builder<TModel>
         */
        public function filterableHaving(string $attribute) {}
    }
}

namespace Illuminate\Support\Facades {

    class DB
    {
        /**
         * Get the appropriate raw SQL based on the database driver.
         *
         * @param string|null $default
         * @param string|null $sqlite
         * @param string|null $mysql
         * @param string|null $pgsql
         * @param string|null $sqlsrv
         * @param string|null $mongodb
         * @return string
         */
        public static function rawMatch(
            ?string $default = null,
            ?string $sqlite = null,
            ?string $mysql = null,
            ?string $pgsql = null,
            ?string $sqlsrv = null,
            ?string $mongodb = null
        ) {}
    }
}
