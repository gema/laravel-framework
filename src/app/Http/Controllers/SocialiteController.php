<?php

namespace GemaDigital\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to the socialite login page.
     */
    public function login(string $driver): RedirectResponse
    {
        return Socialite::driver($driver)->redirect();
    }

    /**
     * Handle the socialite callback.
     */
    public function callback(string $driver): RedirectResponse
    {
        $socialUser = Socialite::driver($driver)->user();

        $user = User::query()
            ->where('email', $socialUser->getEmail())
            ->firstOr(fn () => User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
                'password' => bcrypt(Str::random(16)),
            ]));

        $user->name = $socialUser->getName();
        $user->avatar = $socialUser->getAvatar();
        $user->socialite = [
            ...(array) $user->socialite,
            $driver => [
                'id' => $socialUser->getId(),
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
            ],
        ];

        // Check user domain
        if (in_array(Str::afterLast($user->email, '@'), config('gemadigital.auto_admin_domains', []))) {
            $user->is_admin = true;
        }

        $user->save();

        Auth::login($user, true);

        return redirect(route(config('gemadigital.routes.list.dashboard', 'backpack.dashboard')));
    }
}
