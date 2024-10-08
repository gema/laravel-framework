<?php

namespace GemaDigital\Http\Controllers\Admin\Operations;

use GemaDigital\Http\Controllers\Admin\CrudController;

trait UpdateOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as updateTrait;
    }

    public function update()
    {
        $result = $this->updateTrait();
        $this->sync(CrudController::UPDATED);

        return $result;
    }
}
