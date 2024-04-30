<?php

namespace GemaDigital\Framework\app\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

abstract class BroadcastEvent extends DefaultEvent implements ShouldBroadcast
{
}
