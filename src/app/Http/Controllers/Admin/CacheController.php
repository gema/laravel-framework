<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use Artisan;
use Cache;
use Illuminate\Http\Request;

class CacheController extends CrudController
{
    public function flush(Request $request)
    {
        return json_status(Cache::flush());
    }

    public function config(Request $request)
    {
        return json_status(! Artisan::call('config:cache'));
    }

    public function configClear(Request $request)
    {
        return json_status(! Artisan::call('config:clear'));
    }

    public function route(Request $request)
    {
        return json_status(! Artisan::call('route:cache'));
    }

    public function routeClear(Request $request)
    {
        return json_status(! Artisan::call('route:clear'));
    }

    public function view(Request $request)
    {
        return json_status(! Artisan::call('view:cache'));
    }

    public function viewClear(Request $request)
    {
        return json_status(! Artisan::call('view:clear'));
    }
}
