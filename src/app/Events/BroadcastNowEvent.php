<?php

namespace GemaDigital\Framework\app\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

abstract class BroadcastNowEvent extends DefaultEvent implements ShouldBroadcastNow
{
}
