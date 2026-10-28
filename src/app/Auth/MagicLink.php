<?php

namespace GemaDigital\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class MagicLink
{
    /**
     * Minutes a code and its link last.
     */
    public const MINUTES = 15;

    /**
     * Tries a code takes before it stops working.
     */
    public const ATTEMPTS = 5;

    /**
     * Whether the login by email code is in use. It goes by the routes and not by MAGIC_LINK_LOGIN, so the forms
     * always match them, also with a route cache from before the variable changed.
     */
    public static function active(): bool
    {
        return Route::has('magic-link.code');
    }

    /**
     * A new 6-digit code and single-use link for the email, in place of any earlier ones.
     *
     * @return array{code: string, url: string}
     */
    public static function create(string $email): array
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);
        $expires = now()->addMinutes(self::MINUTES);

        self::forget($email);

        Cache::put(self::key($email), ['code' => self::hash($code), 'token' => hash('sha256', $token)], $expires);
        Cache::put(self::tokenKey(hash('sha256', $token)), $email, $expires);

        return ['code' => $code, 'url' => self::url($token)];
    }

    /**
     * Uses up the email's code: true when it's right, false when it's wrong,
     * null when there's none to check (expired, used or out of tries).
     */
    public static function check(string $email, string $code): ?bool
    {
        $pending = Cache::get(self::key($email));

        if (! is_array($pending)) {
            return null;
        }

        // Counted before comparing, so tries sent at the same time count too
        if (RateLimiter::hit('gemadigital:magic-link:attempts:'.$pending['token'], self::MINUTES * 60) > self::ATTEMPTS) {
            self::forget($email);

            return null;
        }

        if (! hash_equals($pending['code'], self::hash($code))) {
            return false;
        }

        self::forget($email);

        return true;
    }

    /**
     * The email of a valid link, without using it up.
     */
    public static function email(string $token): ?string
    {
        $email = Cache::get(self::tokenKey(hash('sha256', $token)));

        return is_string($email) ? $email : null;
    }

    /**
     * Uses up the link, and the code with it: its email the first time, null after that.
     */
    public static function consume(string $token): ?string
    {
        $email = Cache::pull(self::tokenKey(hash('sha256', $token)));

        if (! is_string($email)) {
            return null;
        }

        self::forget($email);

        return $email;
    }

    /**
     * Drops the email's code and link.
     */
    public static function forget(string $email): void
    {
        $pending = Cache::pull(self::key($email));

        if (is_array($pending)) {
            Cache::forget(self::tokenKey($pending['token']));
        }
    }

    /**
     * Only hashes go to the cache, so it holds no usable codes or links.
     */
    protected static function key(string $email): string
    {
        return 'gemadigital:magic-link:login:'.hash('sha256', Str::lower($email));
    }

    protected static function tokenKey(string $tokenHash): string
    {
        return 'gemadigital:magic-link:token:'.$tokenHash;
    }

    /**
     * With the app key, since a plain hash of 6 digits is easy to reverse.
     */
    protected static function hash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    /**
     * The link on the request's host only when it's the app's domain or a subdomain of it,
     * so a forged Host header can't send someone a link to another site.
     */
    protected static function url(string $token): string
    {
        $appUrl = (string) config('app.url');
        $appHost = (string) parse_url($appUrl, PHP_URL_HOST);
        $host = request()->getHost();

        if ($host === $appHost || Str::endsWith($host, '.'.$appHost)) {
            return route('magic-link', ['token' => $token]);
        }

        return rtrim($appUrl, '/').route('magic-link', ['token' => $token], false);
    }
}
