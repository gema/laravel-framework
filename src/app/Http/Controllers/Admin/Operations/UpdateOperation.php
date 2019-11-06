<?php

namespace gemadigital\framework\App\Http\Controllers\Admin\Operations;

use gemadigital\framework\App\Http\Controllers\Admin\CrudController;

trait UpdateOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as updateTrait;
    }

    function update()
    {
        $result = $this->updateTrait();
        $this->sync(CrudController::UPDATED);

        return $result;
    }
}
