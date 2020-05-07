<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use GemaDigital\Framework\app\Helpers\EnumHelper;

class UserCrudController extends \Backpack\PermissionManager\app\Http\Controllers\UserCrudController
{
    public function setup()
    {
        parent::setup();

        $this->crud->enableExportButtons();
    }

    public function setupListOperation()
    {
        parent::setupListOperation();
    }

    public function setupFields()
    {
        $this->crud->addField([
            'label' => __('Phone'),
            'name' => 'phone',
            'type' => 'text',
        ])->afterField('email');

        $this->crud->addField([
            'name' => 'status',
            'label' => __('Status'),
            'type' => 'select_from_array',
            'options' => EnumHelper::translate('user.status'),
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
    {}
}
