<?php

namespace gemadigital\framework\App\Http\Controllers\Admin\Operations;

use gemadigital\framework\App\Http\Controllers\Admin\CrudController;

trait CreateOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as storeTrait;
    }

    function store()
    {
        $result = $this->storeTrait();
        $this->sync(CrudController::CREATED);

        return $result;
    }
}
