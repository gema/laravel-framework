<?php

namespace GemaDigital\Framework\app\Helpers;

use Config;

class EnumHelper
{
    public static function get($name)
    {
        return Config::get("enums.$name");
    }

    public static function flip($name)
    {
        return array_flip(self::get($name));
    }

    public static function values($name, $join = null)
    {
        $data = array_values(self::get($name));
        return $join ? join($join, $data) : $data;
    }

    public static function keys($name, $join = null)
    {
        $data = array_keys(self::get($name));
        return $join ? join($join, $data) : $data;
    }

    public static function translate($name)
    {
        $enum = [];
        foreach (self::get($name) as $key => $value) {
            $enum[$key] = ucfirst(__($value));
        }

        return $enum;
    }

    public static function joinKeys($name, $join = ',')
    {
        return self::keys($name, $join);
    }

    public static function joinValues($name, $join = ',')
    {
        return self::values($name, $join);
    }

    public static function join($name, $join = ',')
    {
        return self::joinValues($name, $join);
    }
}
