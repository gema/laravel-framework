<?php

namespace GemaDigital\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeUndefinedApiParams
{
    /**
     * Convert frontend placeholder strings to null recursively.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Normalize query/body input
        if ($input = $request->all()) {
            array_walk_recursive($input, static function (&$item): void {
                if ($item === 'undefined' || $item === 'null') {
                    $item = null;
                }
            });

            $request->merge($input);
        }

        // Normalize route parameters
        $route = $request->route();
        foreach ($route->parameters() as $key => $value) {
            if (is_string($value) && ($value === 'undefined' || $value === 'null')) {
                $route->setParameter($key, null);
            }
        }

        return $next($request);
    }
}
