<?php

namespace GemaDigital\Helpers;

use Illuminate\Database\Events\QueryExecuted;

class QueryLogger
{
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
     */
    public static function list(): array
    {
        return self::$queryLogs;
    }
}
