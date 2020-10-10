<?php

namespace GemaDigital\Framework\app\Models\Traits;

use Carbon\Carbon;
use Config;
use Image;
use Storage;
use Str;

trait SaveMedia
{
    public function saveImage($model, $value, $path, $name, $sizes, $quality = 85, $attribute_name = 'image', $disk = 'uploads', $deleteOld = true)
    {
        // Save old name in case of delete
        $oldFile = $model->{$attribute_name};

        // if the image was erased
        if ($value === null) {
            if ($deleteOld) {
                $this->deleteImage($disk, $path, $oldFile, $sizes);
            }

            $model->attributes[$attribute_name] = null;
        }

        // if a base64 was sent, store it in the db
        if (Str::startsWith($value, 'data:image')) {
            $format = substr($value, 11, 3);

            // jpeg exception
            if ($format == 'jpe') {
                $format = 'jpg';
            }

            $timestamp = Carbon::now()->timestamp;
            $filename = Str::slug("$name $timestamp") . ".$format";

            $image = Image::make($value);

            // Save original image
            Storage::disk($disk)->put($path . $filename, $image->stream($format, $quality));
            $model->attributes[$attribute_name] = str_replace(Config::get('app.url'), '', $path . $filename);

            // Save all sizes
            if (!is_array($sizes)) {
                $sizes = [$sizes];
            }

            rsort($sizes);

            foreach ($sizes as $size) {
                if ($image->width() > $size) {
                    $image->resize($size, null, function ($c) {$c->aspectRatio();});
                }

                Storage::disk($disk)->put("$path/$size/$filename", $image->stream($format, $quality));
            }

            // Delete old image
            if ($deleteOld) {
                $this->deleteImage($disk, $path, $oldFile, $sizes);
            }

        } else {
            $model->attributes[$attribute_name] = $value;
        }

        // Clean path
        return $model->attributes[$attribute_name] = $this->cleanPath($model->attributes[$attribute_name], $disk);
    }

    public function deleteImage($disk, $path, $filename, $sizes)
    {
        // Get filename only
        $filename = basename($filename);

        if ($filename) {
            // Delete main image
            Storage::disk($disk)->delete("$path/$filename");

            // Delete sizes
            foreach ($sizes as $size) {
                Storage::disk($disk)->delete("$path/$size/$filename");
            }
        }
    }

    public function cleanPath($path, $disk)
    {
        // Remove URL
        $path = str_replace(Config::get('app.url'), '', $path);

        // Remove Disk
        if ($disk) {
            $path = preg_replace("/\/?$disk\/?/", '', $path);
        }

        // Remove First slash
        $path = preg_replace("/^\//", '', $path);

        return $path;
    }

    public function uploadMultipleFilesToDiskKeepFileName($value, $attribute_name, $disk, $destination_path)
    {
        $request = request();
        $attribute_value = (array) $this->{$attribute_name};
        $files_to_clear = $request->get('clear_' . $attribute_name);

        // if a file has been marked for removal,
        // delete it from the disk and from the db
        if ($files_to_clear) {
            $attribute_value = (array) $this->{$attribute_name};
            foreach ($files_to_clear as $key => $filename) {
                Storage::disk($disk)->delete($filename);
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
