<?php

namespace GemaDigital;

use GemaDigital\Events\DefaultEvent;
use GemaDigital\Http\Middleware\AdminPanelAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Default welcome logic route.
 */
function welcomeRoute(): View|RedirectResponse
{
    if (! Auth::check() || AdminPanelAccess::hasAccess()) {
        return Redirect::to(route('backpack.dashboard'));
    }

    return view('welcome');
}

/**
 * Publish an event to a Redis channel.
 */
function redisPublish(DefaultEvent $event): array
{
    $redis = Redis::connection();
    $status = $redis->publish($event->getChannel(), $event->toJson());

    return [
        'status' => (bool) $status,
        'event' => $event->toArray(),
    ];
}

/**
 * Generates a URL for the given path if it is not already a URL.
 */
function aurl(string $path, ?string $disk = null): string
{
    return str_starts_with($path, 'http') ? $path : ($disk ? Storage::disk($disk)->url($path) : Storage::url($path));
}

/**
 * Generates a sized image URL by replacing the last segment of the path with the given size.
 */
function sized_image(string $path, int $size): string
{
    if (($pos = strrpos($path, '/')) !== false) {
        $path = substr_replace($path, "/$size/", $pos, 1);
    }

    return $path;
}

/**
 * Returns the class name of the given object.
 */
function get_class_name(string $object): string
{
    return (new ReflectionClass($object))->getShortName();
}

/**
 * Memoizes the result of a method call to avoid redundant computations.
 */
function memoize(mixed $target): mixed
{
    static $memo = new WeakMap;

    return new class($target, $memo)
    {
        public function __construct(
            protected $target,
            protected &$memo,
        ) {}

        public function __call($method, $params)
        {
            $this->memo[$this->target] ??= [];

            $signature = $method.crc32(json_encode($params));

            return $this->memo[$this->target][$signature] ??= $this->target->$method(...$params);
        }
    };
}

/**
 * Check if file mime type is Application
 */
function isFileApplication(string $filePath): bool
{
    return isFileType($filePath, 'application');
}

/**
 * Check if file mime type is Audio
 */
function isFileAudio(string $filePath): bool
{
    return isFileType($filePath, 'audio');
}

/**
 * Check if file mime type is Text
 */
function isFileText(string $filePath): bool
{
    return isFileType($filePath, 'text');
}

/**
 * Check if file mime type is Image
 */
function isFileImage(string $filePath): bool
{
    return isFileType($filePath, 'image');
}

/**
 * Check if file mime type is Video
 */
function isFileVideo(string $filePath): bool
{
    return isFileType($filePath, 'video');
}

/**
 * Check if file mime type matches the given type.
 */
function isFileType(string $filePath, string $type): bool
{
    return File::exists($filePath) && Str::startsWith(File::mimeType($filePath), $type);
}

/**
 * Replace the file extension of a given file path with a new extension.
 */
function replaceFileExtension(string $filePath, string $newExtension): string
{
    return substr($filePath, 0, strrpos($filePath, '.') + 1).$newExtension;
}

/**
 * Strip HTML tags from a string or array of strings for Unity compatibility.
 */
function stripTagsForUnity(mixed $item): mixed
{
    if (is_array($item)) {
        foreach ($item as $key => $value) {
            $item[$key] = stripTagsForUnity($value);
        }

        return $item;
    }

    if (isset($item)) {
        $item = html_entity_decode((string) $item);
        $item = str_replace('<br/>', "\n", $item);
        $item = str_replace('<br />', "\n", $item);
        $item = str_replace('</p><p>', "\n\n", $item);
        $item = str_replace('<strong>', '[B]', $item);
        $item = str_replace('</strong>', '[/B]', $item);
        $item = strip_tags($item);
        $item = str_replace('[B]', '<b>', $item);
        $item = str_replace('[/B]', '</b>', $item);
        $item = stripslashes($item);
    }

    return $item;
}
