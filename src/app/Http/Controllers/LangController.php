<?php

namespace GemaDigital\Framework\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Session;

class LangController extends Controller
{
    public function setLang($locale)
    {
        Session::put('locale', $locale);
        return redirect(url()->previous());
    }
}
