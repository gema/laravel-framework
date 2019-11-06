<?php

namespace GemaDigital\Framework\App\Http\Controllers\Admin\Operations;

use GemaDigital\Framework\App\Http\Controllers\Admin\CrudController;

trait BulkDeleteOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation {
        bulkDelete as bulkDeleteTrait;
    }

    function bulkDelete()
    {
        $result = $this->bulkDeleteTrait();
        $this->sync(CrudController::DESTROYED);

        return $result;
    }
}
