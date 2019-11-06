<?php

namespace gemadigital\framework\App\Http\Controllers\Admin\Operations;

use gemadigital\framework\App\Http\Controllers\Admin\CrudController;

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
