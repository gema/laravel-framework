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
            'sql' => self::interpolate($log),
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

    /**
     * The query with the bindings in place of its placeholders, or as it came when their numbers don't match:
     * a ? can also be a PostgreSQL jsonb operator (?, ?|, ?&) or sit in SQL run with DB::unprepared(), such as
     * a function body
     */
    private static function interpolate(QueryExecuted $log): string
    {
        // Dates and booleans as the database gets them: a DateTime has no __toString for vsprintf
        $bindings = $log->connection->prepareBindings($log->bindings);

        if (substr_count($log->sql, '?') !== count($bindings)) {
            return $log->sql;
        }

        return vsprintf(str_replace(['%', '?'], ['%%', '%s'], $log->sql), $bindings);
    }
}
