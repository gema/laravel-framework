<?php

namespace GemaDigital\Models\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * The avatar to show, for the User model: Backpack shows it with 'avatar_type' => 'avatar_url'.
 * The uploaded one (avatar), then the photo of the Microsoft login, then the one of the Google login
 * (both in socialite, saved by the SocialiteController), then Gravatar.
 *
 * @phpstan-ignore trait.unused
 */
trait HasAvatar
{
    /**
     * @return Attribute<string, never>
     */
    public function avatarUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->avatar
            ?: data_get($this->socialite, 'azure.avatar')
            ?: data_get($this->socialite, 'google.avatar')
            ?: 'https://www.gravatar.com/avatar/'.hash('sha256', strtolower(trim((string) $this->email))).'?'.http_build_query([
                's' => 80,
                'd' => config('backpack.base.gravatar_fallback', 'blank'),
            ]));
    }
}
