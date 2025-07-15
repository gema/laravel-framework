<?php

namespace GemaDigital\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

class MaintenanceController extends CrudController
{
    public function up(Request $request): Response
    {
        return response()->apiStatus(! Artisan::call('up'));
    }

    public function down(Request $request): Response
    {
        return response()->apiStatus(! Artisan::call('down', [
            '--allow' => $_SERVER['REMOTE_ADDR'],
        ]));
    }
}
