<?php

namespace GemaDigital\Framework\app\Events;

use GemaDigital\Framework\app\Events\DefaultEvent;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

abstract class BroadcastEvent extends DefaultEvent implements ShouldBroadcast
{

}
