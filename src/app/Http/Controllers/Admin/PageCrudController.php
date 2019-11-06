<?php

namespace gemadigital\framework\App\Http\Controllers\Admin;

class PageCrudController extends \Backpack\PageManager\app\Http\Controllers\Admin\PageCrudController
{
    public function setup($template_name = false)
    {
        parent::setup($template_name);

        if (!is('admin')) {
            $this->crud->denyAccess(['list', 'update']);
        }

        $this->crud->denyAccess(['create', 'delete']);
    }

    public function addDefaultPageFields($template = false)
    {
        $result = parent::addDefaultPageFields($template);
        $this->crud->modifyField('template', ['readonly' => 'readonly', 'style' => 'pointer-events: none;']);
        $this->crud->modifyField('slug', ['attributes' => ['readonly' => 'readonly']]);

        return $result;
    }

    public function update()
    {
        $result = parent::update();

        \Cache::forget('page_{request()->slug}_{request()->locale}');

        return $result;
    }

}
