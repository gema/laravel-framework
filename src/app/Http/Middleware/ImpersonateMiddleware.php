<?php

namespace GemaDigital\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Session::has('impersonated')) {
            Auth::onceUsingId(Session::get('impersonated'));
        }

        return $next($request);
    }
}
