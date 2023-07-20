<?php

namespace GemaDigital\Framework\app\Events;

use GemaDigital\Framework\app\Events\DefaultEvent;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

abstract class BroadcastNowEvent extends DefaultEvent implements ShouldBroadcastNow
{

}
