<?php

namespace GemaDigital\Http\Controllers\Admin\Operations;

use GemaDigital\Http\Controllers\Admin\CrudController;

/** @deprecated Use XDeleteOperation instead. */
/** @phpstan-ignore trait.unused */
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
