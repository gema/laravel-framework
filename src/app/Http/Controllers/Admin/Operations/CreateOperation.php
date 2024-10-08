<?php

namespace GemaDigital\Http\Controllers\Admin\Operations;

use GemaDigital\Http\Controllers\Admin\CrudController;

trait CreateOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as storeTrait;
    }

    public function store()
    {
        $result = $this->storeTrait();
        $this->sync(CrudController::CREATED);

        return $result;
    }
}
