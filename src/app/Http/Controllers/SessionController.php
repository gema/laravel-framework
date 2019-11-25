<?php

namespace GemaDigital\Framework\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;

class SessionController extends Controller
{
    public function flush(Request $request)
    {
        Session::flush();
        return redirect(url()->previous());
    }
}
