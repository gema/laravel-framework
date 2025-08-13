<?php

namespace GemaDigital\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Session::has('impersonated')) {
            Auth::onceUsingId(Session::get('impersonated'));
        }

        return $next($request);
    }
}
