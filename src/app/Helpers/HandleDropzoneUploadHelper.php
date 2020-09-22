<?php

namespace GemaDigital\Framework\app\Helpers;

use App\Http\Requests\DropzoneRequest;
use Exception;
use File;
use Illuminate\Http\Request;
use Image;
use Pawlox\VideoThumbnail\VideoThumbnail;
use Route;
use Storage;

trait HandleDropzoneUploadHelper
{
    public function handleDropzoneUploadRaw(DropzoneRequest $request)
    {
        $params = Route::current()->parameters();

        // Parse args
        $disk = $params['disk'];
        $column = $params['column'];
        $path = str_replace('_', '/', $params['path']);
        $media = $params['media'];
        $sizes = $params['sizes'] ? json_decode($params['sizes']) : [];
        $save_original = $params['save_original'] ?? 0;
        $quality = $params['quality'] ?? 0;

        switch ($media) {
            case 'image':return $this->handleDropzoneUploadImage($disk, $column, $path, $sizes, $save_original, $quality);
            case 'video':return $this->handleDropzoneUploadVideo($disk, $column, $path, $sizes, $save_original, $quality);
        }
    }

    public function handleDropzoneRemoveRaw(Request $request)
    {
        $params = Route::current()->parameters();

        // Parse args
        $disk = $params['disk'];
        $column = $params['column'];
        $path = str_replace('_', '\\', $params['path']);
        $media = $params['media'];
        $sizes = $params['sizes'] ? json_decode($params['sizes']) : [];
        $model = str_replace('_', '\\', $params['model']);

        // Switch media

        switch ($media) {
            case 'image':return $this->handleDropzoneRemoveImage($disk, $column, $path, $sizes, $model);
            case 'video':return $this->handleDropzoneRemoveVideo($disk, $column, $path, $sizes, $model);
        }
    }

    // ----------
    // Image

    public function handleDropzoneRemoveImage($disk, $column, $path, $sizes, $model)
    {
        $id = request()->get('id');
        $filepath = request()->get('filepath');

        try
        {
            $filename = basename($filepath);

            // Remove File from disk
            Storage::disk($disk)->delete("$path/$filename");
            foreach ($sizes as $size) {
                Storage::disk($disk)->delete("$path/$size/$filename");
            }

            // Remove file from DB
            $entity = $model::find($id);

            if ($entity) {
                $imgs = $entity->getAttribute($column);
                if (isset($imgs)) {
                    unset($imgs[array_search($filepath, $imgs)]);

                    $entity->{$column} = array_values($imgs);
                    $entity->save();
                }
            }

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response('Unknown error' . $e, 412);
        }
    }

    public function handleDropzoneUploadImage($disk, $column, $path, $sizes, $save_original, $quality)
    {
        try
        {
            $file = request()->file('file');

            if (!$this->compareMimeTypes($file, ['image'])) {
                return response('Not a valid image type', 412);
            }

            // Image
            $image = Image::make($file);

            // Filename
            $filename = $this->getFileName($file);
            $extension = \File::extension($filename);

            rsort($sizes);

            // Save original or its max width version
            if (!$save_original && $image->width() > $sizes[0]) {
                $image->resize($sizes[0], null, function ($c) {$c->aspectRatio();});
            }
            Storage::disk($disk)->put("$path/$filename", $quality ? $image->stream($extension, $quality) : $image->stream());

            // Sizes
            foreach ($sizes as $size) {
                if ($image->width() > $size) {
                    $image->resize($size, null, function ($c) {$c->aspectRatio();});
                }

                Storage::disk($disk)->put("$path/$size/$filename", $quality ? $image->stream($extension, $quality) : $image->stream());
            }

            return response()->json(['success' => true, 'filename' => "$disk/$path/$filename"]);
        } catch (Exception $e) {
            return response('Unknown error ' . $e, 412);
        }
    }

    // ----------
    // Video

    public function handleDropzoneUploadVideo($disk, $column, $path, $sizes, $save_original, $quality)
    {
        try
        {
            $file = request()->file('file');
            $final_path = Storage::disk($disk)->getDriver()->getAdapter()->getPathPrefix() . $path;

            if (!$this->compareMimeTypes($file, ['video'])) {
                return response('Not a valid video type', 412);
            }

            // Filename
            $filename = $this->getFileName($file);

            Storage::disk($disk)->put("$path/$filename", File::get($file));

            $thumbname = $this->replaceExtension($filename, 'jpg');

            // Main thumbnail
            $thumbnail = new VideoThumbnail();
            $thumbnail->createThumbnail("$final_path/$filename", "$final_path", $thumbname, 2, 256, 256);

            // Other thumbnails
            foreach ($sizes as $size) {
                $thumbnail = new VideoThumbnail();
                if (!file_exists("$final_path/$size")) {
                    mkdir("$final_path/$size");
                }

                $thumbnail->createThumbnail("$final_path/$filename", "$final_path/$size", $thumbname, 2, $size, $size * 3 / 4);
            }

            return response()->json(['success' => true, 'filename' => "$disk/$path/$filename", 'thumbnail' => "$disk/$path/$thumbname"]);
        } catch (Exception $e) {
            return response('Unknown error ' . $e, 412);
        }
    }

    public function handleDropzoneRemoveVideo($disk, $column, $path, $sizes, $model)
    {
        $id = request()->get('id');
        $filepath = request()->get('filepath');

        try
        {
            $filename = basename($filepath);

            // Remove File from disk
            Storage::disk($disk)->delete("$path/$filename");
            foreach ($sizes as $size) {
                Storage::disk($disk)->delete("$path/$size/$filename");
            }

            // Remove possible thumbnail
            $thumbname = $this->replaceExtension($filename, 'jpg');
            Storage::disk($disk)->delete("$path/$thumbname");
            foreach ($sizes as $size) {
                Storage::disk($disk)->delete("$path/$size/$thumbname");
            }

            // Remove file from DB
            $entity = $model::find($id);

            if ($entity) {
                $imgs = $entity->getAttribute($column);
                if (isset($imgs)) {
                    unset($imgs[array_search($filepath, $imgs)]);

                    $entity->{$column} = array_values($imgs);
                    $entity->save();
                }
            }

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response('Unknown error' . $e, 412);
        }
    }

    // ----------
    // Helpers

    private function getFileName($file, $random_name = false)
    {
        $filename = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file->getClientOriginalName()) . '_' . time();
        if ($random_name) {
            $filename = md5($filename);
        }

        return $filename . '.' . $file->extension();
    }

    private function replaceExtension($filename, $extension)
    {
        $info = pathinfo($filename);
        return $info['filename'] . '.' . $extension;
    }

    private function compareMimeTypes($file, $mimes)
    {
        foreach ($mimes as $mime) {
            if (strpos($file->getMimeType(), $mime) === 0) {
                return true;
            }
        }

        return false;
    }
}
