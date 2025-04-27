<?php

namespace GemaDigital;

use GemaDigital\Http\Middleware\AdminPanelAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;


// Routes
function welcomeRoute(): View|RedirectResponse {
    if(! Auth::check() || AdminPanelAccess::hasAccess()) {
        return Redirect::to(route('backpack.dashboard'));
    }

    return view('welcome');
}
