<?php

namespace GemaDigital\Framework\app\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
    public function flush(Request $request): Redirector|RedirectResponse
    {
        Session::flush();

        return redirect(url()->previous());
    }
}
