<?php

namespace GemaDigital\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class CacheController extends Controller
{
    /**
     * php artisan optimize:clear, then php artisan optimize: the application cache goes, and the config, events,
     * routes and views are cached again. Locally it only clears, as the cached config and routes would hide the
     * changes made meanwhile.
     */
    public function clear(): JsonResponse
    {
        // Taken before the commands: on Laravel 12, optimize leaves app() on a fresh copy of the app, which doesn't
        // have the admin's locale
        $translator = app('translator');
        $text = fn (string $key, array $replace = []) => $translator->get("gemadigital::actions.cache.clear.$key", $replace);

        try {
            Artisan::call('optimize:clear');
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => $text('failed', ['error' => $e->getMessage()])], 500);
        }

        if (app()->isLocal()) {
            return response()->json(['message' => $text('cleared')]);
        }

        try {
            Artisan::call('optimize');
        } catch (Throwable $e) {
            report($e);

            // The site works, only without some of the caches
            return response()->json(['message' => $text('not_optimized', ['error' => $e->getMessage()])], 500);
        }

        return response()->json(['message' => $text('optimized')]);
    }
}
