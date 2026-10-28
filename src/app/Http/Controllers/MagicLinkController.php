<?php

namespace GemaDigital\Http\Controllers;

use App\Models\User;
use GemaDigital\Auth\MagicLink;
use GemaDigital\Notifications\MagicLinkNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class MagicLinkController extends Controller
{
    /**
     * The login form, by steps: the email, then the code sent to it, then the name on a first login.
     */
    public function showLoginForm(Request $request): View
    {
        $session = $request->session();

        return $this->page(match (true) {
            $session->has('magic_link.verified') => ['step' => 'name', 'email' => $session->get('magic_link.verified')],
            $session->has('magic_link.email') => ['step' => 'code', 'email' => $session->get('magic_link.email')],
            default => ['step' => 'email'],
        });
    }

    /**
     * Send the code, with a link to log in with one click. The answer is the same whether or not the email can log in.
     */
    public function send(Request $request): RedirectResponse
    {
        $email = (string) $request->validate(['email' => ['required', 'email', 'max:255']])['email'];

        $this->throttle($request, $email);

        // New emails only get a code while registration is open
        if ($this->user($email) || config('gemadigital.registration_open')) {
            $login = MagicLink::create($email);

            try {
                Notification::route('mail', $email)->notify(new MagicLinkNotification($login['code'], $login['url']));
            } catch (Throwable $e) {
                report($e);
                MagicLink::forget($email);

                return $this->toLogin('gemadigital::magic_link.not_sent');
            }
        }

        $request->session()->put('magic_link.email', $email);

        return $this->toLogin();
    }

    /**
     * Log in with the code, in the browser that asked for it.
     */
    public function code(Request $request): RedirectResponse
    {
        $email = $request->session()->get('magic_link.email');
        // The form's 6 boxes (code[]) or the whole code, with or without the email's space
        $code = (string) preg_replace('/\D/', '', implode('', Arr::flatten(Arr::wrap($request->input('code')))));

        if (! is_string($email)) {
            return $this->toLogin();
        }

        // A typo doesn't take one of the code's tries
        if (strlen($code) !== 6) {
            return $this->toLogin()->withErrors(['code' => trans('gemadigital::magic_link.wrong_code')]);
        }

        return match (MagicLink::check($email, $code)) {
            true => $this->verified($request, $email),
            false => $this->toLogin()->withErrors(['code' => trans('gemadigital::magic_link.wrong_code')]),
            null => $this->restart($request)->withErrors(['email' => trans('gemadigital::magic_link.code_expired')]),
        };
    }

    /**
     * The link in the email. In the browser that asked for the code it logs in right away; in any other,
     * only on the page's button, so mail scanners that open links don't use them up.
     */
    public function show(Request $request, string $token): View|RedirectResponse
    {
        $email = MagicLink::email($token);

        if (! $email) {
            return $this->toLogin('gemadigital::magic_link.expired');
        }

        if (Str::lower((string) $request->session()->get('magic_link.email')) === Str::lower($email)) {
            return $this->link($request, $token);
        }

        return $this->page(['step' => 'link', 'email' => $email, 'token' => $token]);
    }

    /**
     * Log in with the link.
     */
    public function link(Request $request, string $token): RedirectResponse
    {
        $email = MagicLink::consume($token);

        return $email ? $this->verified($request, $email) : $this->toLogin('gemadigital::magic_link.expired');
    }

    /**
     * The first login: the name registers the account, without admin permission.
     */
    public function register(Request $request): RedirectResponse
    {
        $email = $request->session()->get('magic_link.verified');

        if (! is_string($email)) {
            return $this->toLogin();
        }

        if (! config('gemadigital.registration_open')) {
            $request->session()->forget('magic_link');

            return $this->toLogin('backpack::base.registration_closed');
        }

        $request->validate(['name' => ['required', 'string', 'max:255']]);

        $user = $this->user($email);

        if (! $user) {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $email,
                'password' => bcrypt(Str::random(16)),
            ]);

            // The code or the link proved the email
            if ($user instanceof MustVerifyEmail) {
                $user->markEmailAsVerified();
            }

            event(new Registered($user));
        }

        return $this->logIn($request, $user);
    }

    /**
     * Back to the email step.
     */
    public function restart(Request $request): RedirectResponse
    {
        $request->session()->forget('magic_link');

        return $this->toLogin();
    }

    /**
     * The email is proven: log in, or ask for the name on a first login.
     */
    protected function verified(Request $request, string $email): RedirectResponse
    {
        $request->session()->forget('magic_link');

        $user = $this->user($email);

        if ($user) {
            return $this->logIn($request, $user);
        }

        if (! config('gemadigital.registration_open')) {
            return $this->toLogin('backpack::base.registration_closed');
        }

        $request->session()->put('magic_link.verified', $email);

        return $this->toLogin();
    }

    protected function logIn(Request $request, User $user): RedirectResponse
    {
        // Backpack's guard, as in its own login
        /** @var StatefulGuard $guard */
        $guard = Auth::guard(config('backpack.base.guard'));
        $guard->login($user, true);

        $request->session()->forget('magic_link');
        $request->session()->regenerate();

        return redirect()->intended(route(config('gemadigital.routes.list.dashboard', 'backpack.dashboard')));
    }

    /**
     * The theme's login page, whose form is the framework's (views/magic_link/theme).
     *
     * @param  array<string, mixed>  $data
     */
    protected function page(array $data): View
    {
        return view('gemadigital::magic_link.login', [
            'title' => trans('backpack::base.login'),
            'username' => 'email',
            ...$data,
        ]);
    }

    /**
     * At most 3 codes per email and 20 per IP every 10 minutes.
     *
     * @throws ValidationException
     */
    protected function throttle(Request $request, string $email): void
    {
        $limits = [
            'gemadigital:magic-link:email:'.sha1(Str::lower($email)) => 3,
            'gemadigital:magic-link:ip:'.$request->ip() => 20,
        ];

        foreach ($limits as $key => $max) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                throw ValidationException::withMessages([
                    'email' => trans('gemadigital::magic_link.throttle', ['minutes' => (int) ceil(RateLimiter::availableIn($key) / 60)]),
                ]);
            }
        }

        foreach (array_keys($limits) as $key) {
            RateLimiter::hit($key, 600);
        }
    }

    protected function user(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /**
     * Back to the login form, with the error, if any, under the email.
     */
    protected function toLogin(?string $errorKey = null): RedirectResponse
    {
        $redirect = redirect()->route(config('gemadigital.routes.list.login', 'backpack.auth.login'));

        return $errorKey ? $redirect->withErrors(['email' => trans($errorKey)]) : $redirect;
    }
}
