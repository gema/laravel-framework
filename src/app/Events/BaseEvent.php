<?php

namespace GemaDigital\Framework\App\Events;

use GemaDigital\Framework\App\Events\SerializesEvents;
use GemaDigital\Framework\App\Events\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BaseEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, SerializesEvents;

    public function broadcastAs()
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
