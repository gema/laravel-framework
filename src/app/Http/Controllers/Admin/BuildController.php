<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use File;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Str;

class BuildController extends Controller
{
    public function build(Request $request)
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

    public function save($data)
    {
        return File::put(config('gemadigital.build.path'), json_encode($data));
    }

    public function shellExec()
    {
        return shell_exec('npm run build');
    }
}
