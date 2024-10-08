<?php

namespace GemaDigital\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Session;

class LangController extends Controller
{
    public function setLang(string $locale): Redirector|RedirectResponse
    {
        Session::put('locale', $locale);

        return redirect(url()->previous());
    }
}
