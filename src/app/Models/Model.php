<?php

namespace GemaDigital\Framework\app\Models;

use \Illuminate\Database\Eloquent\Model as OriginalModel;

class Model extends OriginalModel
{
    /**
     * Events Helper.
     */
    protected static function booted()
    {
        /*
         * Retrieve
         */
        method_exists(__CLASS__, 'retrieved') && static::retrieved(function ($element): void {
            self::call($element, 'afterRetrieve');
        });

        /*
         * Create
         */
        method_exists(__CLASS__, 'creating') && static::creating(function ($element): void {
            self::call($element, 'beforeCreate');
        });

        method_exists(__CLASS__, 'created') && static::created(function ($element): void {
            self::call($element, 'afterCreate');
            self::call($element, 'sync', 'create');
        });

        /*
         * Update
         */
        method_exists(__CLASS__, 'updating') && static::updating(function ($element): void {
            self::call($element, 'beforeUpdate');
        });

        method_exists(__CLASS__, 'updated') && static::updated(function ($element): void {
            self::call($element, 'afterUpdate');
            self::call($element, 'sync', 'update');
        });

        /*
         * Save
         */
        method_exists(__CLASS__, 'saving') && static::saving(function ($element): void {
            self::call($element, 'beforeSave');
        });

        method_exists(__CLASS__, 'saved') && static::saved(function ($element): void {
            self::call($element, 'afterSave');
            self::call($element, 'sync', 'saved');
        });

        /*
         * Delete
         */
        method_exists(__CLASS__, 'deleting') && static::deleting(function ($element): void {
            self::call($element, 'beforeDelete');
            self::call($element, 'sync', 'delete');
        });

        method_exists(__CLASS__, 'deleted') && static::deleted(function ($element): void {
            self::call($element, 'afterDelete');
        });

        /*
         * Retrieve
         */
        method_exists(__CLASS__, 'retrieved') && static::retrieved(function ($element): void {
            self::call($element, 'beforeRetrieved');
        });
    }

    /**
     * Call method if exists.
     */
    protected static function call($element, string $method, string $extra = null): void
    {
        if (method_exists($element, $method)) {
            $element->{$method}($element, $extra);
        }
    }
}
