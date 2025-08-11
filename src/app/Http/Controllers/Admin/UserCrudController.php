<?php

namespace GemaDigital\Http\Controllers\Admin;

use App\Models\User;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\PermissionManager\app\Http\Controllers\UserCrudController as OriginalUserCrudController;
use Illuminate\Support\Facades\Session;

class UserCrudController extends OriginalUserCrudController
{
    public function setup(): void
    {
        parent::setup();

        CRUD::enableExportButtons();
    }

    public function setupListOperation(): void
    {
        CRUD::button('impersonate')
            ->stack('line')
            ->view('crud::buttons.quick')
            ->position('beginning')
            ->meta([
                'label' => ucfirst(__('gemadigital::messages.impersonate')),
                'icon' => 'la la-user-secret',
                'wrapper' => [
                    'element' => 'a',
                    'href' => fn (User $entry): string => route('impersonate', $entry->id),
                ],
            ]);

        CRUD::setAccessCondition('impersonate', function (User $entry) {
            $user = user();
            if (Session::has('impersonator')) {
                $user = User::find(Session::get('impersonator'));
            }

            return isAdmin($user) && $entry->id !== $user->id && $entry->id !== (int) Session::get('impersonated');
        });

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
    public function sync($operation): void {}
}
