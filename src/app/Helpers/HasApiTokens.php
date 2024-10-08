<?php

namespace GemaDigital\Helpers;

use Config;
use Laravel\Passport\HasApiTokens as OriginalHasApiTokens;

trait HasApiTokens
{
    use OriginalHasApiTokens {
        OriginalHasApiTokens::token as parentToken;
    }

    /**
     * Get the current access token being used by the user.
     *
     * @return \Laravel\Passport\Token|null
     */
    public function token()
    {
        return $this->parentToken() ?: $this->createToken(Config::get('app.name'))->accessToken;
    }
}
