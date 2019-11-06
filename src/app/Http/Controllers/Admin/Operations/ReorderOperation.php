<?php

namespace GemaDigital\Framework\App\Http\Controllers\Admin\Operations;

use GemaDigital\Framework\App\Http\Controllers\Admin\CrudController;

trait ReorderOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation {
        saveReorder as saveReorderTrait;
    }

    function saveReorder()
    {
        $result = $this->saveReorderTrait();
        $this->sync(CrudController::REORDERED);

        return $result;
    }
}
