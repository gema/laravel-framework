<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use Artisan;
use Illuminate\Http\Request;

class MaintenanceController extends CrudController
{
    public function up(Request $request)
    {
        return json_status(! Artisan::call('up'));
    }

    public function down(Request $request)
    {
        return json_status(! Artisan::call('down', [
            '--allow' => $_SERVER['REMOTE_ADDR'],
        ]));
    }
}
