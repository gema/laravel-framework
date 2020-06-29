<?php

namespace GemaDigital\Framework\app\Models;

class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * Scopes
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
     * Events Helper
     */
    protected static function booted()
    {
        /**
         * Retrieve
         */
        static::retrieved(function ($element) {
            self::call($element, 'afterRetrieve');
        });

        /**
         * Create
         */
        static::creating(function ($element) {
            self::call($element, 'beforeCreate');
        });

        static::created(function ($element) {
            self::call($element, 'afterCreate');
            self::call($element, 'sync', 'create');
        });

        /**
         * Update
         */
        static::updating(function ($element) {
            self::call($element, 'beforeUpdate');
        });

        static::updated(function ($element) {
            self::call($element, 'afterUpdate');
            self::call($element, 'sync', 'update');
        });

        /**
         * Save
         */
        static::saving(function ($element) {
            self::call($element, 'beforeSave');
        });

        static::saved(function ($element) {
            self::call($element, 'afterSave');
        });

        /**
         * Delete
         */
        static::deleting(function ($element) {
            self::call($element, 'beforeDelete');
            self::call($element, 'sync', 'delete');
        });

        static::deleted(function ($element) {
            self::call($element, 'afterDelete');
        });

        /**
         * Restore
         */
        static::restoring(function ($element) {
            self::call($element, 'beforeRestore');
        });

        static::restored(function ($element) {
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
