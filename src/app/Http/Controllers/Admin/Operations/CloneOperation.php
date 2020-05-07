<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin\Operations;

use GemaDigital\Framework\app\Http\Controllers\Admin\CrudController;

trait CloneOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation {
        clone as cloneTrait;
    }

    function clone ($id) {
        $result = $this->cloneTrait($id);
        $this->sync(CrudController::CLONED);

        return $result;
    }
}
