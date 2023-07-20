<?php

use App\Models\User;
use GemaDigital\Framework\app\Helpers\QueryLogger;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

if (! function_exists('user')) {
    function user(): ?User
    {
        return Auth::user();
    }
}

if (! function_exists('debugMode')) {
    function debugMode(): bool
    {
        return Config::get('app.debug', false);
    }
}

if (! function_exists('api')) {
    function api()
    {
        return app('App\Http\Controllers\Admin\APICrudController');
    }
}

if (! function_exists('data_get_first')) {
    function data_get_first($target, $key, $attribute, $default = 0)
    {
        $value = data_get($target, $key);

        return count($value) ? $value[0]->{$attribute} : $default;
    }
}

if (! function_exists('hasRole')) {
    function hasRole(string $role): bool
    {
        $user = backpack_user() ?: user();

        return $user && $user->hasRole($role);
    }
}

if (! function_exists('hasAnyPermissions')) {
    function hasAnyPermissions(string | array $permissions): bool
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
    function hasAllPermissions(string | array $permissions): bool
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
    function hasPermission(string | array $permissions): bool
    {
        return hasAnyPermissions($permissions);
    }
}

if (! function_exists('admin')) {
    function admin(): bool
    {
        return hasRole('admin');
    }
}

if (! function_exists('restrictTo')) {
    function restrictTo(string | array $roles, string | array $permissions = null): bool
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
            return in_array($session_role, $roles) || sizeof(array_intersect($session_permissions, array_values($permissions)));
        }

        return false;
    }
}

if (! function_exists('is')) {
    function is(string | array $roles, string | array $permissions = null): bool
    {
        return restrictTo($roles, $permissions);
    }
}

if (! function_exists('aurl')) {
    function aurl(string $disk, string $path): string
    {
        return Str::startsWith($path, 'http') ? $path : Storage::disk($disk)->url($path);
    }
}

if (! function_exists('json_response')) {
    function json_response(mixed $data = null, int $code = 0, int $status = 200, mixed $errors = null, $exception = null): Response
    {
        $response = [
            'code' => $code,
            'data' => $data,
            'errors' => $errors,
        ];

        if (debugMode()) {
            $time = (int) ((microtime(true) - LARAVEL_START) * 1e6);
            $timeData = $time > 1e6 ? [$time / 1e6, 's'] : (
                $time > 1e3 ? [$time / 1e3, 'ms'] : (
                    [$time, 'μs']
                )
            );

            $memory = memory_get_peak_usage();
            $memoryData = $memory > 1e6 ? [$memory / 1e6, 'mb'] : (
                $memory > 1e3 ? [$memory / 1e3, 'kb'] : (
                    [$memory, 'b']
                )
            );

            $queries = QueryLogger::list();

            $response = array_merge($response, ['debug' => [
                'time' => [
                    'value' => $timeData[0],
                    'unit' => $timeData[1],
                ],
                'memory' => [
                    'value' => $memoryData[0],
                    'unit' => $memoryData[1],
                ],
                'exception' => $exception,
                'query' => [
                    'count' => count($queries),
                    'time' => collect($queries)->pluck('time')->sum(),
                    'list' => $queries,
                ],
                'post' => request()->request->all(),
            ]]);
        }

        $response = json_encode($response);

        return response($response, $status)
            ->header('Content-Type', 'text/json')
            ->header('Content-Length', strval(strlen($response)));
    }
}

if (! function_exists('json_response_raw')) {
    function json_response_raw(mixed $raw = null, int $code = 0, int $status = 200, mixed $errors = null)
    {
        $response = [
            'code' => $code,
            'data' => 'RAW',
            'errors' => $errors,
        ];

        $response = json_encode($response);
        $response = str_replace('"RAW"', $raw, $response);

        return response($response, $status)
            ->header('Content-Type', 'text/json')
            ->header('Content-Length', strval(strlen($response)));
    }
}

if (! function_exists('json_error')) {
    function json_error(mixed $errors = null, int $code = -1, int $status = 400): Response
    {
        return json_response(null, $code, $status, $errors);
    }
}

if (! function_exists('json_status')) {
    function json_status(bool $status, int $success = 200, int $fail = 400): Response
    {
        return json_response(null, 0, $status ? $success : $fail);
    }
}

if (! function_exists('json_response_pagination')) {
    function json_response_pagination(mixed $data = null, LengthAwarePaginator $pagination, int $code = 0, int $status = 200, mixed $errors = null, $exception = null): Response
    {
        $data = [
            ...$data,
            'pagination' => Arr::only($pagination->toArray(), [
                'from',
                'to',
                'total',
                'per_page',
                'last_page',
                'current_page',
            ]),
        ];

        return json_response($data, $code, $status, $errors, $exception);
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
    function __fallback(string $key, ?string $fallback = null, ?string $locale = null, ?array $replace = []): ?string
    {
        if (\Illuminate\Support\Facades\Lang::has($key, $locale)) {
            return trans($key, $replace, $locale);
        }

        return $fallback;
    }
}

if (! function_exists('get_class_name')) {
    function get_class_name(string $object): string
    {
        return (new \ReflectionClass($object))->getShortName();
    }
}

if (! function_exists('memoize')) {
    function memoize(mixed $target): mixed
    {
        static $memo = new WeakMap;

        return new class($target, $memo)
        {
            function __construct(
                protected $target,
                protected &$memo,
            ) {}

            function __call($method, $params)
            {
                $this->memo[$this->target]??=[];

                $signature = $method.crc32(json_encode($params));

                return $this->memo[$this->target][$signature] ??= $this->target->$method(...$params);
            }
        };
    }
}
