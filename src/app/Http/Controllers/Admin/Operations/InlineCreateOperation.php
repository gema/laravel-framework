<?php

namespace GemaDigital\Framework\app\Http\Controllers\Admin\Operations;

trait InlineCreateOperation
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation {
        storeInlineCreate as storeInlineCreateTrait;
    }

    function storeInlineCreate()
    {
        $result = $this->storeInlineCreateTrait();
        $this->sync(CrudController::CREATED);

        return $result;
    }
}
