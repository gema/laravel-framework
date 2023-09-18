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
        method_exists(__CLASS__, 'retrieved') && static::retrieved(function ($element) {
            self::call($element, 'afterRetrieve');
        });

        /*
         * Create
         */
        method_exists(__CLASS__, 'creating') && static::creating(function ($element) {
            self::call($element, 'beforeCreate');
        });

        method_exists(__CLASS__, 'created') && static::created(function ($element) {
            self::call($element, 'afterCreate');
            self::call($element, 'sync', 'create');
        });

        /*
         * Update
         */
        method_exists(__CLASS__, 'updating') && static::updating(function ($element) {
            self::call($element, 'beforeUpdate');
        });

        method_exists(__CLASS__, 'updated') && static::updated(function ($element) {
            self::call($element, 'afterUpdate');
            self::call($element, 'sync', 'update');
        });

        /*
         * Save
         */
        method_exists(__CLASS__, 'saving') && static::saving(function ($element) {
            self::call($element, 'beforeSave');
        });

        method_exists(__CLASS__, 'saved') && static::saved(function ($element) {
            self::call($element, 'afterSave');
        });

        /*
         * Delete
         */
        method_exists(__CLASS__, 'deleting') && static::deleting(function ($element) {
            self::call($element, 'beforeDelete');
            self::call($element, 'sync', 'delete');
        });

        method_exists(__CLASS__, 'deleted') && static::deleted(function ($element) {
            self::call($element, 'afterDelete');
        });

        /*
         * Retrieve
         */
        method_exists(__CLASS__, 'retrieved') && static::retrieved(function ($element) {
            self::call($element, 'beforeRetrieved');
        });
    }

    protected static function call($element, $method, $extra = null)
    {
        if (method_exists($element, $method)) {
            $element->{$method}($element, $extra);
        }
    }
}
