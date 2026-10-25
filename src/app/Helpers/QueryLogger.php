<?php

namespace GemaDigital\Helpers;

use Illuminate\Database\Events\QueryExecuted;

class QueryLogger
{
    /**
     * @var array<int, array{sql: string, time: float|null}>
     */
    private static array $queryLogs = [];

    /**
     * Logs a query
     */
    public static function log(QueryExecuted $log): void
    {
        self::$queryLogs[] = [
            'sql' => vsprintf(str_replace(['%', '?'], ['%%', '%s'], $log->sql), $log->bindings),
            'time' => $log->time,
        ];
    }

    /**
     * Get query Log
     *
     * @return array<int, array{sql: string, time: float|null}>
     */
    public static function list(): array
    {
        return self::$queryLogs;
    }
}
