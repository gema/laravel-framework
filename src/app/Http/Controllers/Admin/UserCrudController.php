<?php

namespace gemadigital\framework\App\Http\Controllers\Admin;

use gemadigital\framework\App\Helpers\EnumHelper;

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

        // Filters
        $this->crud->addFilter([
            'name' => 'roles',
            'type' => 'select2_multiple',
            'label' => ucfirst(__('backpack::permissionmanager.roles')),
            'placeholder' => __('Select a role'),
        ],
            EnumHelper::translate('user.roles'),
            function ($values) {
                $this->crud->query->whereHas('roles', function ($query) use ($values) {
                    $query
                        ->selectRaw('role_id')
                        ->whereIn('role_id', json_decode($values));
                });
            });

        $this->crud->addFilter([
            'name' => 'permissions',
            'type' => 'select2_multiple',
            'label' => ucfirst(__('backpack::permissionmanager.permission_plural')),
            'placeholder' => __('Select a permission'),
        ],
            EnumHelper::translate('user.permissions'),
            function ($values) {
                $this->crud->query->whereHas('permissions', function ($query) use ($values) {
                    $query
                        ->selectRaw('permission_id')
                        ->whereIn('permission_id', json_decode($values));
                });
            });
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
}
