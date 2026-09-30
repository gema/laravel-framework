<?php

namespace GemaDigital\Http\Middleware;

use Backpack\CRUD\app\Http\Middleware\AuthenticateSession as BackpackAuthenticateSession;
use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;

/**
 * Backpack compares the session and "remember me" cookie against the raw password hash, but Laravel
 * stores an HMAC of it (cookie since 12.45, session since 13.33 - laravel/framework#61594), which logs
 * every user out right after login. The service provider binds this class in place of Backpack's one,
 * it can be removed once Backpack supports the HMAC format.
 *
 * @see https://github.com/Laravel-Backpack/CRUD/issues/6085
 */
class AuthenticateSession extends BackpackAuthenticateSession
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    #[\Override]
    public function handle($request, Closure $next)
    {
        if (! $request->hasSession() || ! $this->user || ! $this->user->getAuthPassword()) {
            return $next($request);
        }

        if ($this->guard()->viaRemember()) {
            $passwordHash = explode('|', (string) $request->cookies->get($this->guard()->getRecallerName()))[2] ?? null;

            if (! $passwordHash || ! $this->validatePasswordHash($this->user->getAuthPassword(), $passwordHash)) {
                $this->logout($request);
            }
        }

        $key = 'password_hash_'.backpack_guard_name();

        if (! $request->session()->has($key)) {
            $this->storePasswordHashInSession($request);
        }

        // Accepts both the HMAC and the raw hash (older sessions, Backpack's password change)
        if (! $this->validatePasswordHash($this->user->getAuthPassword(), (string) $request->session()->get($key))) {
            $this->logout($request);
        }

        return tap($next($request), function () use ($request): void {
            if (! is_null($this->guard()->user())) {
                $this->storePasswordHashInSession($request);
            }
        });
    }

    /**
     * Store the HMAC of the user's current password hash in the session, as the session guard does.
     *
     * @param  Request  $request
     */
    #[\Override]
    protected function storePasswordHashInSession($request)
    {
        if (! $this->user) {
            return;
        }

        $request->session()->put(
            'password_hash_'.backpack_guard_name(),
            $this->guard()->hashPasswordForCookie($this->user->getAuthPassword()),
        );
    }

    /**
     * Get the Backpack session guard.
     */
    #[\Override]
    protected function guard(): SessionGuard
    {
        $guard = $this->auth->guard(backpack_guard_name());

        assert($guard instanceof SessionGuard);

        return $guard;
    }
}
