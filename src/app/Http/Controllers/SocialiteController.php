<?php

namespace GemaDigital\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as OAuthTwoUser;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to the socialite login page.
     */
    public function login(string $driver): RedirectResponse
    {
        abort_unless(self::isAvailable($driver), 404);

        return Socialite::driver($driver)->redirect();
    }

    /**
     * Handle the socialite callback.
     */
    public function callback(string $driver): RedirectResponse
    {
        abort_unless(self::isAvailable($driver), 404);

        $socialUser = Socialite::driver($driver)->user();
        $avatar = $driver === 'azure' ? $this->azureAvatar($socialUser) : $socialUser->getAvatar();
        $inAllowedDomains = in_array(Str::afterLast($socialUser->getEmail(), '@'), config('gemadigital.auto_admin_domains', []));

        // Find user
        $user = User::query()
            ->where('email', $socialUser->getEmail())
            ->first();

        if (! $user) {
            if (! config('gemadigital.registration_open') && ! $inAllowedDomains) {
                // The login form only shows errors of its fields
                $field = function_exists('backpack_authentication_column') ? backpack_authentication_column() : 'email';

                return redirect()
                    ->route(config('gemadigital.routes.list.login', 'backpack.auth.login'))
                    ->withErrors([$field => 'Registration is closed']);
            }

            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(16)),
            ]);
        }

        $user->name = $socialUser->getName();

        // The avatar column is the uploaded one, which the login doesn't touch: its photo goes in socialite,
        // after the uploaded one in avatar_url (HasAvatar). Older logins copied the photo there, so that copy goes
        if ($user->avatar && in_array($user->avatar, array_column((array) $user->socialite, 'avatar'), true)) {
            $user->avatar = null;
        }

        $user->socialite = [
            ...(array) $user->socialite,
            $driver => [
                'id' => $socialUser->getId(),
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'avatar' => $avatar,
            ],
        ];

        // Check user domain
        if ($inAllowedDomains) {
            $user->is_admin = true;
        }

        $user->save();

        Auth::login($user, true);

        return redirect(route(config('gemadigital.routes.list.dashboard', 'backpack.dashboard')));
    }

    /**
     * Microsoft has no public photo URL: Graph only gives the photo with a token. So the avatar is a small photo,
     * read with the user's own token (User.Read, which the login asks for), as a data URI.
     */
    protected function azureAvatar(SocialUser $socialUser): ?string
    {
        if (! $socialUser instanceof OAuthTwoUser) {
            return null;
        }

        // A Graph that doesn't answer doesn't stop the login
        $photo = rescue(fn () => Http::withToken($socialUser->token)->timeout(5)->get('https://graph.microsoft.com/v1.0/me/photos/96x96/$value'));

        return $photo?->successful()
            ? 'data:'.($photo->header('Content-Type') ?: 'image/jpeg').';base64,'.base64_encode($photo->body())
            : null;
    }

    /**
     * Whether the Socialite driver can be used: Socialite is installed and, for azure,
     * socialiteproviders/microsoft-azure too (the framework registers its driver).
     */
    public static function isAvailable(string $driver): bool
    {
        return class_exists(Socialite::class) && match ($driver) {
            'azure' => class_exists('SocialiteProviders\Azure\Provider'),
            default => true,
        };
    }
}
