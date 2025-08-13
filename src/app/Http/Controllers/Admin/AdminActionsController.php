<?php

namespace GemaDigital\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class AdminActionsController extends Controller
{
    public function actions(): View
    {
        return view('gemadigital::admin.actions', []);
    }
}
