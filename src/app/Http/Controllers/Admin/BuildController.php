<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BuildController extends Controller
{
    public function build(Request $request): string|false|null
    {
        $data = [];
        $classList = config('gemadigital.build.classes');

        foreach ($classList as $class => $resource) {
            $name = Str::of(get_class_name($class))->lower()->plural();
            $content = $resource::collection($class::all());

            $data[strval($name)] = $content;
        }

        // Save file
        $this->save($data);

        // run build command
        return $this->shellExec();
    }

    public function save(mixed $data): int|bool
    {
        return File::put(config('gemadigital.build.path'), json_encode($data));
    }

    public function shellExec(): string|false|null
    {
        return shell_exec('npm run build');
    }
}
