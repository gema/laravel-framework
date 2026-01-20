<?php

namespace GemaDigital\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use ReflectionClass;

trait HasJsonExports
{
    public static function bootHasJsonExports(): void
    {
        $exportHelper = function (Model $targetModel): void {
            foreach (get_class_methods($targetModel) as $method) {
                if (! str_starts_with($method, 'export') || ! str_ends_with($method, 'Json')) {
                    continue;
                }

                $name = Str::of($method)
                    ->substr(6, -4)
                    ->snake();

                $data = $targetModel->$method();

                $path = config('gemadigital.jsonExports.path', storage_path('data'))."/{$name}.json";

                if (! file_exists(dirname($path))) {
                    mkdir(dirname($path), 0755, true);
                }

                if (file_exists($path)) {
                    unlink($path);
                }

                file_put_contents(
                    filename: $path,
                    data: $data->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                );
            }
        };

        $triggerExport = function (Model $model) use ($exportHelper): void {
            $exportHelper($model);

            if (property_exists($model, 'triggerJsonExport')) {
                $reflection = new ReflectionClass($model);
                $property = $reflection->getProperty('triggerJsonExport');
                $property->setAccessible(true);
                $classes = $property->getValue($model);

                if (! is_array($classes)) {
                    $classes = [$classes];
                }

                foreach ($classes as $class) {
                    $exportHelper(new $class);
                }
            }

            if ($webhook = config('gemadigital.jsonExports.webhook')) {
                Http::post($webhook);
            }
        };

        static::saved($triggerExport);
        static::deleted($triggerExport);
    }
}
