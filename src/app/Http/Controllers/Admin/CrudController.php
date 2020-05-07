<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin;

use GemaDigital\Framework\app\Helpers\HandleDropzoneUploadHelper;
use Illuminate\Http\Request;

class CrudController extends \Backpack\CRUD\app\Http\Controllers\CrudController
{
    use HandleDropzoneUploadHelper;

    const CREATED = 'create';
    const CLONED = 'clone';
    const DESTROYED = 'destroy';
    const REORDERED = 'reorder';
    const UPDATED = 'update';

    // Hack to access setup without touching the setup()
    public function setupConfigurationForCurrentOperation()
    {
        parent::setupConfigurationForCurrentOperation();

        // Check authorization for cRUD
        $this->crud->id = $this->crud->getCurrentEntryId();

        if ($this->crud->id && !$this->authorize($this->crud->id)) {
            abort(401);
        }
    }

    public function authorize($id)
    {
        return true;
    }

    public function wantsJSON()
    {
        return $this->request && strpos($this->request->headers->get('accept'), 'application/json') === 0;
    }

    private $i = 0;
    public function separator($title = '')
    {
        return $this->crud->addField([
            'name' => 'separator' . $this->i++,
            'type' => 'custom_html',
            'value' => $title ? "<hr /><h2>$title</h2>" : '<hr />',
            'wrapperAttributes' => [
                'style' => 'margin:0',
            ],
        ]);
    }

    public function getEntryID()
    {
        preg_match('/\w+\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
        return $matches && sizeof($matches) > 1 ? intval($matches[1]) : null;
    }

    public function getEntry()
    {
        return $this->crud->model::where('id', $this->getEntryID())->first() ?: null;
    }

    // Overrides to deal with cache
    public function sync($operation)
    {}
}
