<?php

namespace GemaDigital\Http\Controllers\Admin\Operations;

use GemaDigital\Http\Controllers\Admin\CrudController;

/** @phpstan-ignore trait.unused */
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
