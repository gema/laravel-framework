<?php

namespace GemaDigital\Http\Middleware;

use Backpack\CRUD\app\Http\Middleware\CheckIfAdmin as BaseCheckIfAdmin;
use Closure;
use Illuminate\Http\Request;

class AdminPanelAccess extends BaseCheckIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Boot the access check
        $this->bootAccess($request);

        if (! $this->hasAccess($request)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response(trans('Unauthorized'), 401);
            } else {
                return redirect()->guest(route(config('gemadigital.routes.list.login', 'backpack.auth.login')));
            }
        }

        return $next($request);
    }

    /**
     * Check if the user is an admin.
     */
    public function bootAccess(Request $request): void
    {
        // This method is intentionally left empty.
        // It can be used to perform any bootstrapping logic if needed.
    }

    /**
     * Check if the user is an admin.
     * This method is used to determine if the user has access to the admin panel.
     * It's meant to be overridden in child classes if needed.
     */
    public function hasAccess(Request $request): bool
    {
        return isAdmin($request->user());
    }

    /**
     * Check if the user is an admin.
     * This static method can be used to check access without needing an instance of the class.
     */
    public static function checkAccess(): bool
    {
        return (new self)->hasAccess(request());
    }
}
