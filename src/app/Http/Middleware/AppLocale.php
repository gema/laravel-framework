<?php

namespace GemaDigital\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AppLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale =
            // Request header
            $request->headers->get('locale') ??

            // User data
            $request->user()->data->locale ??

            // HTTP header
            $this->getLocaleFromHttpHeader($request);

        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Get the locale from the
     */
    private function getLocaleFromHttpHeader(Request $request): ?string
    {
        if (Session::has('locale')) {
            return Session::get('locale');
        }

        /** @var array<int,string> $appLocales */
        $appLocales = (array) config('app.locales', []);
        $rawAccept = $request->server('HTTP_ACCEPT_LANGUAGE');
        if (is_array($rawAccept)) {
            $rawAccept = reset($rawAccept) ?: '';
        }
        $acceptLanguage = is_string($rawAccept) ? $rawAccept : '';
        /** @var array<int,string> $requestLocales */
        $requestLocales = preg_split('/,|;/', $acceptLanguage) ?: [];

        foreach ($requestLocales as $locale) {
            if (in_array($locale, $appLocales, true)) {
                Session::put('locale', $locale);

                return $locale;
            }
        }

        return null;
    }
}
