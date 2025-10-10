<?php

namespace GemaDigital\Helpers;

use Illuminate\Database\Events\QueryExecuted;

class QueryLogger
{
    /**
     * @var array<int, array<string, float|string>>
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
     * @return array<int, array<string, float|string>>
     */
    public static function list(): array
    {
        return self::$queryLogs;
    }
}
