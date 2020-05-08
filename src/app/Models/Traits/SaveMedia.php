<?php

namespace GemaDigital\Framework\app\Models\Traits;

use Carbon\Carbon;
use Config;
use Image;
use Storage;
use Str;

trait SaveMedia
{
    public function saveImage($model, $value, $path, $name, $sizes, $quality = 85, $attribute_name = 'image', $disk = 'uploads')
    {
        // if the image was erased
        if ($value == null) {
            Storage::disk($disk)->delete($model->{$attribute_name});
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

            return $model->attributes[$attribute_name];
        } else {
            $model->attributes[$attribute_name] = $value;
        }

        // Clean path
        return $model->attributes[$attribute_name] = $this->cleanPath($model->attributes[$attribute_name], $disk);
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
}
