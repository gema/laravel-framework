<?php

namespace gemadigital\framework\App\Http\Controllers\Admin\Operations;

use gemadigital\framework\App\Http\Controllers\Admin\CrudController;

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
