<?php

namespace GemaDigital\Http\Controllers\Admin\Operations;

use GemaDigital\Http\Controllers\Admin\CrudController;

/** @phpstan-ignore trait.unused */
trait CloneOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation {
        clone as cloneTrait;
    }

    public function clone($id)
    {
        $result = $this->cloneTrait($id);
        $this->sync(CrudController::CLONED);

        return $result;
    }
}
