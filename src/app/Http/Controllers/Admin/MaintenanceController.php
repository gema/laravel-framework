<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

class MaintenanceController extends CrudController
{
    public function up(Request $request): Response
    {
        return json_status(! Artisan::call('up'));
    }

    public function down(Request $request): Response
    {
        return json_status(! Artisan::call('down', [
            '--allow' => $_SERVER['REMOTE_ADDR'],
        ]));
    }
}
