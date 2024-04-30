<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\PageManager\app\Http\Controllers\Admin\PageCrudController as OriginalPageCrudController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class PageCrudController extends OriginalPageCrudController
{
    public function setup(bool $template_name = false): void
    {
        parent::setup();

        if (! is('admin')) {
            CRUD::denyAccess(['list', 'update']);
        }

        CRUD::denyAccess(['create', 'delete']);
    }

    public function addDefaultPageFields($template = false): void
    {
        parent::addDefaultPageFields($template);

        CRUD::modifyField('template', ['readonly' => 'readonly', 'style' => 'pointer-events: none;']);
        CRUD::modifyField('slug', ['attributes' => ['readonly' => 'readonly']]);
    }

    public function update(): JsonResponse|RedirectResponse
    {
        $result = parent::update();

        $slug = request()->slug;
        $locale = request()->locale;
        Cache::forget("page_{$slug}_{$locale}");

        return $result;
    }
}
