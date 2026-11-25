<?php

namespace GemaDigital\Http\Controllers\Admin;

use Illuminate\Foundation\Http\MaintenanceModeBypassCookie;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class MaintenanceController extends Controller
{
    public function up(): Response
    {
        return response()->apiStatus(! Artisan::call('up'));
    }

    /**
     * Everyone else gets the 503. This admin's browser gets the bypass cookie of a random secret, valid for
     * 12 hours, to keep seeing the site and turn the maintenance off. Laravel checks the cookie before
     * decrypting the cookies, so the provider keeps it out of EncryptCookies.
     */
    public function down(): Response
    {
        $secret = Str::random(32);

        return response()->apiStatus(! Artisan::call('down', ['--secret' => $secret]))
            ->withCookie(MaintenanceModeBypassCookie::create($secret));
    }
}
