<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\PermissionManager\app\Http\Controllers\UserCrudController as OriginalUserCrudController;

class UserCrudController extends OriginalUserCrudController
{
    public function setup(): void
    {
        parent::setup();

        CRUD::enableExportButtons();
    }

    public function setupListOperation(): void
    {
        parent::setupListOperation();
    }

    public function setupFields(): void
    {
        CRUD::addField([
            'label' => __('Phone'),
            'name' => 'phone',
            'type' => 'text',
        ])->afterField('email');

        CRUD::addField([
            'name' => 'status',
            'label' => __('Status'),
            'type' => 'select_from_array',
            'options' => [
                0 => ucfirst(__('active')),
                1 => ucfirst(__('inactive')),
            ],
            'allows_null' => false,
        ])->afterField('phone');
    }

    public function setupCreateOperation(): void
    {
        parent::setupCreateOperation();

        $this->setupFields();
    }

    public function setupUpdateOperation(): void
    {
        parent::setupUpdateOperation();

        $this->setupFields();
    }

    // Overrides to deal with cache
    public function sync($operation): void
    {
    }
}
