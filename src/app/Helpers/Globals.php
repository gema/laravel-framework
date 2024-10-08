<?php

namespace GemaDigital\Helpers;

class Globals
{
    public static function set(string $key, mixed $value): void
    {
        $GLOBALS['gemadigital'][$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $GLOBALS['gemadigital'][$key] ?? $default;
    }
}
