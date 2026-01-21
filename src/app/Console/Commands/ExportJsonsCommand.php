<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class ExportJsonsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:jsons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all JSONs defined in HasJsonExports trait';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $modelsPath = app_path('Models');

        if (! File::exists($modelsPath)) {
            $this->error('Models directory not found.');

            return;
        }

        $modelFiles = File::allFiles($modelsPath);

        foreach ($modelFiles as $file) {
            $className = 'App\\Models\\'.$file->getFilenameWithoutExtension();

            if (! class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);
            if (! $reflection->isInstantiable() || ! $reflection->isSubclassOf(Model::class)) {
                continue;
            }

            $traits = class_uses_recursive($className);
            $usesHasJsonExports = false;

            foreach ($traits as $trait) {
                if (str_ends_with($trait, 'HasJsonExports')) {
                    $usesHasJsonExports = true;
                    break;
                }
            }

            if ($usesHasJsonExports) {
                $this->info("Exporting for model: $className");

                /** @var Model */
                $model = new $className;
                $this->exportModel($model);
            }
        }
    }

    protected function exportModel(Model $targetModel): void
    {
        // @phpstan-ignore method.notFound
        foreach ($targetModel->getJsonExportMethods() as $name => $method) {
            $this->line("  - Generating $name.json");

            $data = $targetModel->$method();

            $path = config('gemadigital.jsonExports.path', storage_path('data'))."/{$name}.json";

            if (! file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            file_put_contents(
                $path,
                $data->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            );
        }
    }
}
