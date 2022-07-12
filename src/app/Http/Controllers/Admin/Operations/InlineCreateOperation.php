<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin\Operations;

use GemaDigital\Framework\app\Http\Controllers\Admin\CrudController;

trait InlineCreateOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation {
        storeInlineCreate as storeInlineCreateTrait;
    }

    public function storeInlineCreate()
    {
        $result = $this->storeInlineCreateTrait();
        $this->sync(CrudController::CREATED);

        return $result;
    }
}
