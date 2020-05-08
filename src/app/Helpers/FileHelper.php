<?php

namespace GemaDigital\Framework\app\Helpers;

use Str;
use File;

class FileHelper
{
    // Check if file mime type is Application
    public static function isApplication($file_path)
    {
        return self::isType($file_path, 'application');
    }

    // Check if file mime type is Audio
    public static function isAudio($file_path)
    {
        return self::isType($file_path, 'audio');
    }

    // Check if file mime type is Text
    public static function isText($file_path)
    {
        return self::isType($file_path, 'text');
    }

    // Check if file mime type is Image
    public static function isImage($file_path)
    {
        return self::isType($file_path, 'image');
    }

    // Check if file mime type is Video
    public static function isVideo($file_path)
    {
        return self::isType($file_path, 'video');
    }

    private static function isType($file_path, $type)
    {
        return File::exists($file_path) && Str::startsWith(File::mimeType($file_path), $type);
    }

    public static function replaceExtension($file_path, $newExtension)
    {
        return substr($file_path, 0, strrpos($file_path, '.') + 1) . $newExtension;
    }
}
