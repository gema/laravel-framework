<?php

namespace GemaDigital\Framework\App\Http\Controllers\Admin\Operations;

use GemaDigital\Framework\App\Http\Controllers\Admin\CrudController;

trait BulkCloneOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkCloneOperation {
        bulkClone as bulkCloneTrait;
    }

    function bulkClone()
    {
        $result = $this->bulkCloneTrait();
        $this->sync(CrudController::CLONED);

        return $result;
    }
}
