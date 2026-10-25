<?php

namespace GemaDigital\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    /**
     * Only lets admins through, even when the project's AdminPanelAccess lets other users into the panel.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! isAdmin($request->user())) {
            abort(403);
        }

        return $next($request);
    }
}
