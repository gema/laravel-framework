<?php

namespace GemaDigital\Framework\app\Models;

use Str;

class Model extends \Illuminate\Database\Eloquent\Model
{
    // Scopes
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

    // Link Helper
    public function getLink($entity)
    {
        if (!$entity) {
            return '-';
        }

        $class = strtolower(class_basename($entity));
        return "<a href='/admin/$class/{$entity->id}/edit'>" . Str::limit($entity->name, 60, '...') . '</a>';
    }

    // Upload Multiple Files Helper
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
