<?php

namespace GemaDigital\Http\Controllers\Admin\Operations;

trait AllOperations
{
    use BulkCloneOperation;
    use BulkDeleteOperation;
    use CloneOperation;
    use CreateOperation;
    use DeleteOperation;
    use FetchOperation;
    use InlineCreateOperation;
    use ListOperation;
    use ReorderOperation;
    use ShowOperation;
    use UpdateOperation;
}
