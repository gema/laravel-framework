<?php

namespace GemaDigital\Http\Controllers\Traits;

use DateTimeInterface;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens as OriginalHasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

trait HasApiTokensTrait
{
    use OriginalHasApiTokens;

    /**
     * Create a new personal access token for the user.
     */
    public function createToken(
        string $name = 'default',
        array $extras = [],
        array $abilities = ['*'],
        ?DateTimeInterface $expiresAt = null
    ): NewAccessToken {
        /** @var PersonalAccessToken */
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(40)),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
            ...$extras,
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }
}
