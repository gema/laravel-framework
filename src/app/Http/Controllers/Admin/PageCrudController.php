<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class PageCrudController extends \Backpack\PageManager\app\Http\Controllers\Admin\PageCrudController
{
    public function setup($template_name = false)
    {
        parent::setup($template_name);

        if (!is('admin')) {
            CRUD::denyAccess(['list', 'update']);
        }

        CRUD::denyAccess(['create', 'delete']);
    }

    public function addDefaultPageFields($template = false)
    {
        $result = parent::addDefaultPageFields($template);
        CRUD::modifyField('template', ['readonly' => 'readonly', 'style' => 'pointer-events: none;']);
        CRUD::modifyField('slug', ['attributes' => ['readonly' => 'readonly']]);

        return $result;
    }

    public function update()
    {
        $result = parent::update();

        $slug = request()->slug;
        $locale = request()->locale;
        \Cache::forget("page_{$slug}_{$locale}");

        return $result;
    }
}
