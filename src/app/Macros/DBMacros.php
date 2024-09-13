<?php

namespace GemaDigital\Framework\app\Macros;

use Illuminate\Support\Facades\DB;

class DBMacros
{
    public static function register(): void
    {
        DB::macro('rawMatch', function (
            string $sqlite = '',
            string $mysql = '',
            string $pgsql = '',
            string $sqlsrv = '',
            string $mongodb = '',
        ): string {
            return ${DB::connection()->getName()};
        });
    }
}
