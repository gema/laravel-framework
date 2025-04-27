<?php

use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

if (! function_exists('user')) {
    function user()
    {
        return Auth::user();
    }
}

if (! function_exists('debugMode')) {
    /**
     * @deprecated
     * use config('app.debug', false) instead
     */
    function debugMode(): bool
    {
        return Config::get('app.debug', false);
    }
}

if (! function_exists('api')) {
    function api(): mixed
    {
        return app('App\Http\Controllers\Admin\APICrudController');
    }
}

if (! function_exists('hasRole')) {
    /**
     * @deprecated
     * use impersonate instead
     */
    function hasRole(string $role): bool
    {
        $user = backpack_user() ?: user();

        return $user?->hasRole($role);
    }
}

if (! function_exists('hasAnyPermissions')) {
    /**
     * @deprecated
     * use impersonate instead
     */
    function hasAnyPermissions(string|array $permissions): bool
    {
        $user = backpack_user() ?: user();

        if ($user) {
            if (! is_array($permissions)) {
                $permissions = [$permissions];
            }

            foreach ($permissions as $permission) {
                if ($user->checkPermissionTo($permission, backpack_guard_name())) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (! function_exists('hasAllPermissions')) {
    /**
     * @deprecated
     * use impersonate instead
     */
    function hasAllPermissions(string|array $permissions): bool
    {
        $user = backpack_user() ?: user();

        if ($user) {
            if (! is_array($permissions)) {
                $permissions = [$permissions];
            }

            $value = true;
            foreach ($permissions as $permission) {
                $value &= $user->checkPermissionTo($permission, backpack_guard_name());
            }

            return $value;
        }

        return false;
    }
}

if (! function_exists('hasPermission')) {
    /**
     * @deprecated
     * use impersonate instead
     */
    function hasPermission(string|array $permissions): bool
    {
        return hasAnyPermissions($permissions);
    }
}

if (! function_exists('admin')) {
    function admin(): bool
    {
        return user()?->hasRole('admin');
    }
}

if (! function_exists('restrictTo')) {
    /**
     * @deprecated
     * use impersonate instead
     */
    function restrictTo(string|array $roles, string|array|null $permissions = null): bool
    {
        $session_role = Session::get('role', null);
        $session_permissions = Session::get('permissions', null);

        // Default
        if (! $session_role && ! $session_permissions) {
            return ($roles && hasRole($roles)) || ($permissions && hasPermission($permissions));
        }

        // View as
        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        // View as Role only
        if ($session_role && (! $session_permissions || ! $permissions)) {
            return in_array($session_role, $roles);
        }

        // View as Role and Permissions
        elseif ($session_role && $session_permissions && $permissions) {
            return in_array($session_role, $roles) || count(array_intersect($session_permissions, array_values($permissions)));
        }

        return false;
    }
}

if (! function_exists('is')) {
    function is(string|array $roles, string|array|null $permissions = null): bool
    {
        return user()?->hasRole($roles) || user()?->hasAnyPermission($permissions);
    }
}

if (! function_exists('aurl')) {
    function aurl(string $disk, string $path): string
    {
        return str_starts_with($path, 'http') ? $path : Storage::disk($disk)->url($path);
    }
}

if (! function_exists('json_response')) {
    /**
     * @deprecated
     * use response()->api(...) instead
     */
    function json_response(mixed $data = null, int $code = 0, int $status = 200, mixed $errors = null, $exception = null): Response
    {
        return response()->api($data, $code, $status, $errors, $exception);
    }
}

if (! function_exists('json_response_raw')) {
    /**
     * @deprecated
     * use response()->apiRaw(...) instead
     */
    function json_response_raw(mixed $raw = null, int $code = 0, int $status = 200, mixed $errors = null): Response
    {
        return response()->apiRaw($raw, $code, $status, $errors);
    }
}

if (! function_exists('json_error')) {
    /**
     * @deprecated
     * use response()->api(...) instead
     */
    function json_error(mixed $errors = null, int $code = -1, int $status = 400): Response
    {
        return response()->api(null, $code, $status, $errors);
    }
}

if (! function_exists('json_status')) {
    /**
     * @deprecated
     * use response()->api(...) instead
     */
    function json_status(bool $status, int $success = 200, int $fail = 400): Response
    {
        return response()->api(null, 0, $status ? $success : $fail);
    }
}

if (! function_exists('json_response_pagination')) {
    /**
     * @deprecated
     * use response()->apiPaginated(...) instead
     */
    function json_response_pagination(mixed $data = null, ?LengthAwarePaginator $pagination = null, int $code = 0, int $status = 200, mixed $errors = null, $exception = null): Response
    {
        return response()->apiPaginated($data, $pagination, $code, $status, $errors, $exception);
    }
}

if (! function_exists('sized_image')) {
    function sized_image(string $path, int $size): string
    {
        if (($pos = strrpos($path, '/')) !== false) {
            $path = substr_replace($path, "/$size/", $pos, 1);
        }

        return $path;
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

if (! function_exists('get_class_name')) {
    function get_class_name(string $object): string
    {
        return (new ReflectionClass($object))->getShortName();
    }
}

if (! function_exists('memoize')) {
    function memoize(mixed $target): mixed
    {
        static $memo = new WeakMap;

        return new class($target, $memo)
        {
            public function __construct(
                protected $target,
                protected &$memo,
            ) {}

            public function __call($method, $params)
            {
                $this->memo[$this->target] ??= [];

                $signature = $method.crc32(json_encode($params));

                return $this->memo[$this->target][$signature] ??= $this->target->$method(...$params);
            }
        };
    }
}
