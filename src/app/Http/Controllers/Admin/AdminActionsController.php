<?php

namespace GemaDigital\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminActionsController extends Controller
{
    public function actions(): View
    {
        return view('gemadigital::admin.actions', []);
    }

    public function terminal(): View
    {
        return view('gemadigital::admin.terminal', []);
    }

    public function terminalRun(Request $request): void
    {
        if (admin()) {
            echo shell_exec($request->input('cmd'));
        }
    }
}
