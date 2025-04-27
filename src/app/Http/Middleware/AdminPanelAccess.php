<?php

namespace App\Http\Middleware;

use Backpack\CRUD\app\Http\Middleware\CheckIfAdmin as BaseCheckIfAdmin;
use Closure;

class AdminPanelAccess extends BaseCheckIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! self::hasAccess()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response(trans('backpack::base.unauthorized'), 401);
            } else {
                return redirect()->guest(backpack_url('login'));
            }
        }

        return $next($request);
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public static function hasAccess(): bool
    {
        return request()->user()->hasRole('admin');
    }
}
