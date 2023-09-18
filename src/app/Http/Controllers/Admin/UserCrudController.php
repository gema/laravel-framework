<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\PermissionManager\app\Http\Controllers\UserCrudController as OriginalUserCrudController;
use GemaDigital\Framework\app\Helpers\EnumHelper;

class UserCrudController extends OriginalUserCrudController
{
    public function setup()
    {
        parent::setup();

        CRUD::enableExportButtons();
    }

    public function setupListOperation()
    {
        parent::setupListOperation();
    }

    public function setupFields()
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

    public function setupCreateOperation()
    {
        parent::setupCreateOperation();

        $this->setupFields();
    }

    public function setupUpdateOperation()
    {
        parent::setupUpdateOperation();

        $this->setupFields();
    }

    // Overrides to deal with cache
    public function sync($operation)
    {
    }
}
