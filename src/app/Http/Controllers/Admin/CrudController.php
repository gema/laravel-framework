<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController as OriginalCrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Database\Eloquent\Model;

class CrudController extends OriginalCrudController
{
    public const CREATED = 'create';
    public const CLONED = 'clone';
    public const DESTROYED = 'destroy';
    public const REORDERED = 'reorder';
    public const UPDATED = 'update';

    // Hack to access setup without touching the setup()
    public function setupConfigurationForCurrentOperation(): void
    {
        parent::setupConfigurationForCurrentOperation();

        // Check authorization for CRUD
        $id = $this->crud->getCurrentEntryId();

        if ($id && ! $this->authorize($id)) {
            abort(401);
        }
    }

    public function authorize(int $id): bool
    {
        return true;
    }

    public function wantsJSON(): bool
    {
        return strpos(request()->headers->get('accept'), 'application/json') === 0;
    }

    private $i = 0;
    public function separator(?string $title = null): CrudPanel
    {
        return CRUD::addField([
            'name' => 'separator'.$this->i++,
            'type' => 'custom_html',
            'value' => '<hr />'.($title ? "<h2>$title</h2>" : ''),
            'wrapperAttributes' => [
                'style' => 'margin:0',
            ],
        ]);
    }

    public function getEntryID(): int
    {
        preg_match('/\w+\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);

        return $matches && sizeof($matches) > 1 ? intval($matches[1]) : null;
    }

    public function getEntry(): Model
    {
        return $this->crud->model::where('id', $this->getEntryID())->first() ?: null;
    }

    // Overrides to deal with cache
    public function sync(string $operation): void
    {
    }
}
