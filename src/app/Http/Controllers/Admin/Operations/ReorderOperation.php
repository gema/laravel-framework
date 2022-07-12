<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin\Operations;

use GemaDigital\Framework\app\Http\Controllers\Admin\CrudController;

trait ReorderOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation {
        saveReorder as saveReorderTrait;
    }

    public function saveReorder()
    {
        $result = $this->saveReorderTrait();
        $this->sync(CrudController::REORDERED);

        return $result;
    }
}
