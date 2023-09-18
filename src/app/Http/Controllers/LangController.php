<?php

namespace GemaDigital\Framework\app\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;

class LangController extends Controller
{
    public function setLang($locale)
    {
        Session::put('locale', $locale);

        return redirect(url()->previous());
    }
}
