<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin\Operations;

use GemaDigital\Framework\app\Http\Controllers\Admin\CrudController;

trait DeleteOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation {
        destroy as destroyTrait;
    }

    public function destroy($id)
    {
        $result = $this->destroyTrait($id);
        $this->sync(CrudController::DESTROYED);

        return $result;
    }
}
