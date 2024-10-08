<?php

namespace GemaDigital\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FileHelper
{
    // Check if file mime type is Application
    public static function isApplication(string $filePath): bool
    {
        return self::isType($filePath, 'application');
    }

    // Check if file mime type is Audio
    public static function isAudio(string $filePath): bool
    {
        return self::isType($filePath, 'audio');
    }

    // Check if file mime type is Text
    public static function isText(string $filePath): bool
    {
        return self::isType($filePath, 'text');
    }

    // Check if file mime type is Image
    public static function isImage(string $filePath): bool
    {
        return self::isType($filePath, 'image');
    }

    // Check if file mime type is Video
    public static function isVideo(string $filePath): bool
    {
        return self::isType($filePath, 'video');
    }

    private static function isType(string $filePath, string $type): bool
    {
        return File::exists($filePath) && Str::startsWith(File::mimeType($filePath), $type);
    }

    public static function replaceExtension(string $filePath, string $newExtension): string
    {
        return substr($filePath, 0, strrpos($filePath, '.') + 1).$newExtension;
    }
}
