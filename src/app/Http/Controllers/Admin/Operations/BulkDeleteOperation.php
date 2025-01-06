<?php

namespace GemaDigital\Http\Controllers\Admin\Operations;

use GemaDigital\Http\Controllers\Admin\CrudController;

/** @phpstan-ignore trait.unused */
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
