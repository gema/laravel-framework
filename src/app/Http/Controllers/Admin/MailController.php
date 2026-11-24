<?php

namespace GemaDigital\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class MailController extends Controller
{
    /**
     * Runs mail:test to the address typed in the button's prompt.
     */
    public function test(Request $request): JsonResponse
    {
        $email = $request->validate(['email' => 'required|email'])['email'];

        // On failure, what the command says: the mailer, where it went and why it wasn't sent
        if (Artisan::call('mail:test', ['to' => $email])) {
            return response()->json(['message' => trim(Artisan::output())], 500);
        }

        return response()->json(['message' => __('gemadigital::actions.mail.test.success', [
            'email' => $email,
            'mailer' => config('mail.default'),
        ])]);
    }
}
