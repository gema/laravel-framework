<?php

namespace GemaDigital\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CacheController extends CrudController
{
    public function flush(Request $request): Response
    {
        return json_status(Cache::flush());
    }

    public function config(Request $request): Response
    {
        return json_status(! Artisan::call('config:cache'));
    }

    public function configClear(Request $request): Response
    {
        return json_status(! Artisan::call('config:clear'));
    }

    public function route(Request $request): Response
    {
        return json_status(! Artisan::call('route:cache'));
    }

    public function routeClear(Request $request): Response
    {
        return json_status(! Artisan::call('route:clear'));
    }

    public function view(Request $request): Response
    {
        return json_status(! Artisan::call('view:cache'));
    }

    public function viewClear(Request $request): Response
    {
        return json_status(! Artisan::call('view:clear'));
    }
}
