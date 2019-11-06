<?php

namespace GemaDigital\Framework\App\Http\Controllers\Admin\Operations;

use GemaDigital\Framework\App\Http\Controllers\Admin\CrudController;

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
