<?php

namespace GemaDigital\Helpers;

use Illuminate\Support\Facades\Config;
use Laravel\Passport\HasApiTokens as OriginalHasApiTokens;
use Laravel\Passport\Token;

/** @phpstan-ignore trait.unused */
trait HasApiTokens
{
    use OriginalHasApiTokens {
        OriginalHasApiTokens::token as parentToken;
    }

    /**
     * Get the current access token being used by the user.
     *
     * @return ?Token
     */
    public function token()
    {
        return $this->parentToken() ?: $this->createToken(Config::get('app.name'))->accessToken;
    }
}
