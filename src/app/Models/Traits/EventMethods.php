<?php

namespace GemaDigital\Models\Traits;

trait EventMethods
{
    /**
     * Boot the event methods.
     */
    protected static function bootEventMethods(): void
    {
        /*
         * Retrieve
         */
        // @phpstan-ignore function.alreadyNarrowedType
        method_exists(self::class, 'retrieved') && static::retrieved(function ($element): void {
            self::call($element, 'beforeRetrieved');
            self::call($element, 'afterRetrieve');
        });

        /*
         * Create
         */
        // @phpstan-ignore function.alreadyNarrowedType
        method_exists(self::class, 'creating') && static::creating(function ($element): void {
            self::call($element, 'beforeCreate');
        });

        // @phpstan-ignore function.alreadyNarrowedType
        method_exists(self::class, 'created') && static::created(function ($element): void {
            self::call($element, 'afterCreate');
            self::call($element, 'sync', 'create');
        });

        /*
         * Update
         */
        // @phpstan-ignore function.alreadyNarrowedType
        method_exists(self::class, 'updating') && static::updating(function ($element): void {
            self::call($element, 'beforeUpdate');
        });

        // @phpstan-ignore function.alreadyNarrowedType
        method_exists(self::class, 'updated') && static::updated(function ($element): void {
            self::call($element, 'afterUpdate');
            self::call($element, 'sync', 'update');
        });

        /*
         * Save
         */
        // @phpstan-ignore function.alreadyNarrowedType
        method_exists(self::class, 'saving') && static::saving(function ($element): void {
            self::call($element, 'beforeSave');
        });

        // @phpstan-ignore function.alreadyNarrowedType
        method_exists(self::class, 'saved') && static::saved(function ($element): void {
            self::call($element, 'afterSave');
            self::call($element, 'sync', 'saved');
        });

        /*
         * Delete
         */
        // @phpstan-ignore function.alreadyNarrowedType
        method_exists(self::class, 'deleting') && static::deleting(function ($element): void {
            self::call($element, 'beforeDelete');
            self::call($element, 'sync', 'delete');
        });

        // @phpstan-ignore function.alreadyNarrowedType
        method_exists(self::class, 'deleted') && static::deleted(function ($element): void {
            self::call($element, 'afterDelete');
        });
    }

    /**
     * Call method if exists.
     */
    protected static function call($element, string $method, ?string $extra = null): void
    {
        if (method_exists($element, $method)) {
            $element->{$method}($element, $extra);
        }
    }
}
