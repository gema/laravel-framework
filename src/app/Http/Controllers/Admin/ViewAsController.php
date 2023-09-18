<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;

class ViewAsController extends Controller
{
    public function viewAsRole($role)
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

    public function viewAsPermission($permission, $state)
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

            if (sizeof($permissions)) {
                Session::put('permissions', $permissions);
            } else {
                Session::remove('permissions');
            }
        } else {
            $this->clearAll();
        }

        return redirect(url()->previous());
    }

    private function clearAll()
    {
        Session::remove('role');
        Session::remove('permissions');
    }
}
