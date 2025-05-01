<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

if (! function_exists('user')) {
    function user()
    {
        return Auth::user() ?? backpack_user();
    }
}

if (! function_exists('debugMode')) {
    function debugMode(): bool
    {
        return Config::get('app.debug', false);
    }
}

if (! function_exists('admin')) {
    function admin(): bool
    {
        return user()?->hasRole('admin');
    }
}

if (! function_exists('is')) {
    function is(string|array $roles, string|array|null $permissions = null): bool
    {
        return user()?->hasRole($roles) || user()?->hasAnyPermission($permissions);
    }
}

if (! function_exists('__fallback')) {
    function __fallback(
        string $key,
        ?string $fallback = null,
        ?string $locale = null,
        array $replace = [],
    ): ?string {
        if (Lang::has($key, $locale)) {
            return trans($key, $replace, $locale);
        }

        return $fallback;
    }
}
