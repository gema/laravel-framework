<?php

namespace GemaDigital\Framework\app\Events;

use GemaDigital\Framework\app\Events\DefaultEvent;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * @deprecated 10.3
 * Should use the DefaultEvent
 */
abstract class BaseEvent extends DefaultEvent implements ShouldBroadcast
{

}
