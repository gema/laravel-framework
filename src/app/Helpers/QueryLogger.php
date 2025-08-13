<?php

namespace GemaDigital\Helpers;

use Illuminate\Database\Events\QueryExecuted;

class QueryLogger
{
    /**
     * @var array<string, string|float>
     */
    private static array $queryLogs = [];

    /**
     * Logs a query
     */
    public static function log(QueryExecuted $log): void
    {
        array_push(self::$queryLogs, [
            'sql' => vsprintf(str_replace(['%', '?'], ['%%', '%s'], $log->sql), $log->bindings),
            'time' => $log->time,
        ]);
    }

    /**
     * Get query Log
     *
     * @return array<string, string|float>
     */
    public static function list(): array
    {
        return self::$queryLogs;
    }
}
