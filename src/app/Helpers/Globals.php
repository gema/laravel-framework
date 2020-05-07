<?php

namespace GemaDigital\Framework\app\Helpers;

class Globals
{
    public static function set($key, $value)
    {
        $GLOBALS['gemadigital'][$key] = $value;
    }

    public static function get($key, $default = null)
    {
        return $GLOBALS['gemadigital'][$key] ?? $default;
    }
}
