<?php

namespace GemaDigital\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;

class ImpersonateController extends Controller
{
    public function impersonate(Request $request, int $id): RedirectResponse
    {
        if (! $this->canImpersonate()) {
            abort(403);
        }

        // save data to session
        Session::put('impersonated', $id);
        if (! Session::has('impersonator')) {
            Session::put('impersonator', $request->user()->id);
        }

        return redirect(route('backpack.dashboard'));
    }

    public function leave(): RedirectResponse
    {
        // clear session data
        Session::forget('impersonator');
        Session::forget('impersonated');

        return redirect(route('backpack.dashboard'));
    }

    public function canImpersonate(): bool
    {
        return request()->user()->hasRole('admin');
    }
}
