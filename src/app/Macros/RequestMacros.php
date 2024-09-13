<?php

namespace GemaDigital\Framework\app\Macros;

use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\Request;
use Laravel\Sanctum\TransientToken;

class RequestMacros
{
    public static function register(): void
    {
        Request::macro('currentAccessToken', function (): PersonalAccessToken|TransientToken|null {
            /** @var PersonalAccessToken|TransientToken|null $accessToken */
            $accessToken = $this->user('sanctum')?->currentAccessToken();

            return $accessToken;
        });
    }
}
