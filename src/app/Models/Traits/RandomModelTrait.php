<?php

namespace GemaDigital\Models\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @mixin HasFactory
 */
trait RandomModelTrait
{
    /**
     * Gets a random entry
     */
    public static function random(): ?self
    {
        /** @phpstan-ignore-next-line */
        return static::inRandomOrder()->first();
    }

    /**
     * Gets a random entry or a new one in case none found
     */
    public static function randomOrNew(): self
    {
        /** @phpstan-ignore-next-line */
        return static::inRandomOrder()->first() ?? self::factory();
    }
}
