<?php

namespace GemaDigital\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Session;

class ViewAsController extends Controller
{
    public function viewAsRole(string $role): Redirector|RedirectResponse
    {
        if (! admin()) {
            abort(403);
        }

        if ($role !== 'admin') {
            Session::put('role', $role);
        } else {
            $this->clearAll();
            $this->clearAll();
        }

        return redirect(url()->previous());
    }

    public function viewAsPermission(string $permission, $state): Redirector|RedirectResponse
    {
        if (! admin()) {
            abort(403);
        }

        if ($permission !== 'all') {
            $permissions = Session::get('permissions', []);

            if ($state) {
                array_push($permissions, $permission);
            } else {
                unset($permissions[array_search($permission, $permissions)]);
            }

            if (count($permissions)) {
                Session::put('permissions', $permissions);
            } else {
                Session::remove('permissions');
            }
        } else {
            $this->clearAll();
        }

        return redirect(url()->previous());
    }

    private function clearAll(): void
    {
        Session::remove('role');
        Session::remove('permissions');
    }
}
