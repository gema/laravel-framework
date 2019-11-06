<?php

namespace GemaDigital\Framework\App\Http\Controllers\Admin\Operations;

use GemaDigital\Framework\App\Http\Controllers\Admin\CrudController;

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
