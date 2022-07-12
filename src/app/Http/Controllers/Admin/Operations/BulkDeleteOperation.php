<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin\Operations;

use GemaDigital\Framework\app\Http\Controllers\Admin\CrudController;

trait BulkDeleteOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation {
        bulkDelete as bulkDeleteTrait;
    }

    public function bulkDelete()
    {
        $result = $this->bulkDeleteTrait();
        $this->sync(CrudController::DESTROYED);

        return $result;
    }
}
