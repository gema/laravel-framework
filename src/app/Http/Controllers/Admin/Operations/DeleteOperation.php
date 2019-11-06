<?php

namespace GemaDigital\Framework\App\Http\Controllers\Admin\Operations;

use GemaDigital\Framework\App\Http\Controllers\Admin\CrudController;

trait DeleteOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation {
        destroy as destroyTrait;
    }

    function destroy($id)
    {
        $result = $this->destroyTrait($id);
        $this->sync(CrudController::DESTROYED);

        return $result;
    }
}
