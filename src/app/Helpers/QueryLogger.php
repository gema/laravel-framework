<?php

namespace GemaDigital\Framework\app\Helpers;

use Illuminate\Database\Events\QueryExecuted;

class QueryLogger
{
    private static array $queryLogs = [];

    /**
     * Logs a query
     *
     * @param QueryExecuted $log
     * @return void
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
     * @return array
     */
    public static function list(): array
    {
        return self::$queryLogs;
    }
}
