<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin\Operations;

use GemaDigital\Framework\app\Http\Controllers\Admin\CrudController;

trait BulkCloneOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkCloneOperation {
        bulkClone as bulkCloneTrait;
    }

    public function bulkClone()
    {
        $result = $this->bulkCloneTrait();
        $this->sync(CrudController::CLONED);

        return $result;
    }
}
