<?php

namespace GemaDigital\Http\Middleware;

use Backpack\CRUD\app\Http\Middleware\CheckIfAdmin as BaseCheckIfAdmin;
use Closure;

class CheckIfAdmin extends BaseCheckIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! is('admin')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response(trans('backpack::base.unauthorized'), 401);
            } else {
                return redirect()->guest(backpack_url('login'));
            }
        }

        return $next($request);
    }
}
