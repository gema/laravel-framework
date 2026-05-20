<?php

namespace GemaDigital\Http\Controllers\Admin\Operations;

use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use GemaDigital\Http\Controllers\Admin\CrudController;

/** @phpstan-ignore trait.unused */
trait XDeleteOperation
{
    use DeleteOperation {
        destroy as destroyTrait;
    }

    public function destroy($id)
    {
        $result = $this->destroyTrait($id);
        $this->sync(CrudController::DESTROYED);

        return $result;
    }
}
