<?php

namespace gemadigital\framework\App\Http\Controllers\Admin\Operations;

trait AllOperations
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use CloneOperation;
    use DeleteOperation;
    use BulkDeleteOperation;
    use BulkCloneOperation;
    use ReorderOperation;
}
