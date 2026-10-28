<?php

namespace GemaDigital\Http\Controllers\Admin;

use App\Models\User;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\PermissionManager\app\Http\Controllers\UserCrudController as OriginalUserCrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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

        CRUD::addField([
            'label' => __('Admin'),
            'name' => 'is_admin',
            'type' => 'checkbox',
        ])->afterField('status');

        // With the login by email (MAGIC_LINK_LOGIN) nobody uses a password
        if (config('gemadigital.magic_link_login')) {
            CRUD::removeFields(['password', 'password_confirmation']);
        }
    }

    public function setupCreateOperation(): void
    {
        parent::setupCreateOperation();

        $this->setupFields();

        // Backpack only saves the fields' values: the random password of store() goes too
        if (config('gemadigital.magic_link_login')) {
            CRUD::setOperationSetting('strippedRequest', fn (Request $request): array => $request->only([...CRUD::getAllFieldNames(), 'password']));
        }
    }

    public function store()
    {
        // With the login by email the new user gets a random password, as on a first login by email
        if (config('gemadigital.magic_link_login')) {
            $password = Str::random(32);
            $this->crud->getRequest()->merge(['password' => $password, 'password_confirmation' => $password]);
        }

        return parent::store();
    }

    public function setupUpdateOperation(): void
    {
        parent::setupUpdateOperation();

        $this->setupFields();
    }

    // Overrides to deal with cache
    public function sync($operation): void {}
}
