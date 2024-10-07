<?php

namespace GemaDigital\Framework\app\Macros;

use Illuminate\Support\Facades\DB;

class DBMacros
{
    public static function register(): void
    {
        DB::macro('rawMatch', function (
            ?string $default = null,
            ?string $sqlite = null,
            ?string $mysql = null,
            ?string $pgsql = null,
            ?string $sqlsrv = null,
            ?string $mongodb = null,
        ): string {
            return ${DB::connection()->getName()} ?? $default;
        });
    }
}
