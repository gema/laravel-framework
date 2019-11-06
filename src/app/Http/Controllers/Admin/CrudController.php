<?php

namespace GemaDigital\Framework\App\Http\Controllers\Admin;

use GemaDigital\Framework\App\Helpers\HandleDropzoneUploadHelper;
use Illuminate\Http\Request;

class CrudController extends \Backpack\CRUD\app\Http\Controllers\CrudController
{
    use HandleDropzoneUploadHelper;

    const CREATED = 'create';
    const CLONED = 'clone';
    const DESTROYED = 'destroy';
    const REORDERED = 'reorder';
    const UPDATED = 'update';

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

    // Overrides to deal with cache
    public function sync($operation)
    {}
}
