<?php

namespace GemaDigital\Models;

use Illuminate\Database\Eloquent\Model as OriginalModel;

class Model extends OriginalModel
{
    use Traits\EventMethods;
}
