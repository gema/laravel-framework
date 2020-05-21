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
        method_exists(__CLASS__, 'retrieved') && static::retrieved(function ($element) {
            self::call($element, 'afterRetrieve');
        });

        /**
         * Create
         */
        method_exists(__CLASS__, 'creating') && static::creating(function ($element) {
            self::call($element, 'beforeCreate');
            self::call($element, 'beforeSync', 'create');
        });

        method_exists(__CLASS__, 'created') && static::created(function ($element) {
            self::call($element, 'afterCreate');
            self::call($element, 'afterSync', 'create');
        });

        /**
         * Update
         */
        method_exists(__CLASS__, 'updating') && static::updating(function ($element) {
            self::call($element, 'beforeUpdate');
            self::call($element, 'beforeSync', 'update');
        });

        method_exists(__CLASS__, 'updated') && static::updated(function ($element) {
            self::call($element, 'afterUpdate');
            self::call($element, 'afterSync', 'update');
        });

        /**
         * Save
         */
        method_exists(__CLASS__, 'saving') && static::saving(function ($element) {
            self::call($element, 'beforeSave');
            self::call($element, 'beforeSync', 'save');
        });

        method_exists(__CLASS__, 'saved') && static::saved(function ($element) {
            self::call($element, 'afterSave');
            self::call($element, 'afterSync', 'save');
        });

        /**
         * Delete
         */
        method_exists(__CLASS__, 'deleting') && static::deleting(function ($element) {
            self::call($element, 'beforeDelete');
            self::call($element, 'beforeSync', 'delete');
        });

        method_exists(__CLASS__, 'deleted') && static::deleted(function ($element) {
            self::call($element, 'afterDelete');
            self::call($element, 'afterSync', 'delete');
        });

        /**
         * Restore
         */
        method_exists(__CLASS__, 'restoring') && static::restoring(function ($element) {
            self::call($element, 'beforeRestore');
            self::call($element, 'beforeSync', 'restore');
        });

        method_exists(__CLASS__, 'restored') && static::restored(function ($element) {
            self::call($element, 'afterRestore');
            self::call($element, 'afterSync', 'restore');
        });
    }

    protected static function call($element, $method, $extra = null)
    {
        if (method_exists($element, $method)) {
            $element->{$method}($element, $extra);
        }
    }

    /**
     * Helpers
     */
    public function uploadMultipleFilesToDiskKeepFileName($value, $attribute_name, $disk, $destination_path)
    {
        $request = \Request::instance();
        $attribute_value = (array) $this->{$attribute_name};
        $files_to_clear = $request->get('clear_' . $attribute_name);

        // if a file has been marked for removal,
        // delete it from the disk and from the db
        if ($files_to_clear) {
            $attribute_value = (array) $this->{$attribute_name};
            foreach ($files_to_clear as $key => $filename) {
                \Storage::disk($disk)->delete($filename);
                $attribute_value = array_where($attribute_value, function ($value, $key) use ($filename) {
                    return $value != $filename;
                });
            }
        }

        // if a new file is uploaded, store it on disk and its filename in the database
        if ($request->hasFile($attribute_name)) {
            foreach ($request->file($attribute_name) as $file) {
                if ($file->isValid()) {
                    // 1. Generate a new file name
                    $new_file_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.' . time() . '.' . $file->getClientOriginalExtension();

                    // 2. Move the new file to the correct path
                    $file_path = $file->storeAs($destination_path, $new_file_name, $disk);

                    // 3. Add the public path to the database
                    $attribute_value[] = $file_path;
                }
            }
        }

        $this->attributes[$attribute_name] = json_encode($attribute_value);
    }
}
