<?php

namespace GemaDigital\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Gemadigital\get_class_name;

class BuildController extends Controller
{
    public function build(Request $request): Response
    {
        $data = [];
        $classList = config('gemadigital.build.classes');

        foreach ($classList as $class => $resource) {
            $name = Str::of(get_class_name($class))->lower()->plural();
            $content = $resource::collection($class::all());

            $data[strval($name)] = $content;
        }

        // Save file
        return response()->apiStatus($this->save($data) !== false);
    }

    public function save(mixed $data): int|bool
    {
        return File::put(config('gemadigital.build.path'), json_encode($data) ?: '');
    }
}
