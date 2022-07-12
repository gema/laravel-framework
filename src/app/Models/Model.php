<?php

namespace GemaDigital\Framework\app\Models;

class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * Scopes.
     */
    public static function findById(string $id)
    {
        return static::where('id', $id)->firstOrFail();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'publish');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('lft', 'ASC');
    }

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
         * Restore
         */
        method_exists(__CLASS__, 'restoring') && static::restoring(function ($element) {
            self::call($element, 'beforeRestore');
        });

        method_exists(__CLASS__, 'restored') && static::restored(function ($element) {
            self::call($element, 'afterRestore');
            self::call($element, 'sync', 'restore');
        });
    }

    protected static function call($element, $method, $extra = null)
    {
        if (method_exists($element, $method)) {
            $element->{$method}($element, $extra);
        }
    }
}
