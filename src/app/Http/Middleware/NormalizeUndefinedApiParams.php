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
        $input = $request->all();

        array_walk_recursive($input, static function (&$item): void {
            if ($item === 'undefined' || $item === 'null') {
                $item = null;
            }
        });

        $request->merge($input);

        return $next($request);
    }
}