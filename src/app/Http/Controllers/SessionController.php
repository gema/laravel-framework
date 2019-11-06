<?php

namespace GemaDigital\Framework\App\Http\Controllers;

use Illuminate\Http\Request;
use Session;

class SessionController extends CrudController
{
    public function flush(Request $request)
    {
        Session::flush();
        return redirect(url()->previous());
    }
}
