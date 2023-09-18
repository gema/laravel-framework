<?php

namespace GemaDigital\Framework\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
    public function flush(Request $request)
    {
        Session::flush();

        return redirect(url()->previous());
    }
}
