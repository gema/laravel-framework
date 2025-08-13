<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

if (! function_exists('user')) {
    function user(): ?User
    {
        return Auth::user();
    }
}

if (! function_exists('debugMode')) {
    function debugMode(): bool
    {
        return config('app.debug', false);
    }
}

if (! function_exists('isAdmin')) {
    function isAdmin(?User $user = null): bool
    {
        $user ??= user();

        return $user->is_admin ?? false;
    }
}

if (! function_exists('is')) {
    /**
     * @param  string|array<string>  $roles
     * @param  string|array<string>|null  $permissions
     */
    function is(string|array $roles, string|array|null $permissions = null): bool
    {
        return user()?->hasRole($roles) || user()?->hasAnyPermission($permissions);
    }
}

if (! function_exists('__fallback')) {
    /**
     * @param  array<string, string>  $replace
     */
    function __fallback(
        string $key,
        ?string $fallback = null,
        ?string $locale = null,
        array $replace = [],
    ): ?string {
        if (Lang::has($key, $locale)) {
            $trans = trans($key, $replace, $locale);

            return is_array($trans) ? $trans[0] : $trans;
        }

        return $fallback;
    }
}
