<?php

namespace gemadigital\framework\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminActionsController extends Controller
{
    public function actions()
    {
        return view('gemadigital::admin.actions', []);
    }

    public function terminal()
    {
        return view('gemadigital::admin.terminal', []);
    }

    public function terminalRun(Request $request)
    {
        if (admin()) {
            echo shell_exec($request->input('cmd'));
        }
    }

    public function symlink()
    {
        return view('gemadigital::admin.symlink', []);
    }

    public function symlinkRun(Request $request)
    {
        if (admin()) {
            echo symlink(base_path() . $request->input('target'), base_path() . $request->input('link')) ? 'Success' : 'Error';
        }
    }

}
