<?php

namespace GemaDigital\Framework\app\Events;

use Auth;
use Carbon\Carbon;
use GemaDigital\Framework\app\Events\SerializesEvents;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class DefaultEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    use SerializesEvents;

    public $channel;
    public $user;
    public $timestamp;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $channel)
    {
        $this->channel = $channel;
        $this->user = $this->getUserData();
        $this->timestamp = Carbon::now();
    }

    public function broadcastAs()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function broadcastOn()
    {
        return new Channel($this->channel);
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getUserData()
    {
        $user = Auth::user();

        return $user ? (object) $user->only(['id', 'name', 'email']) : null;
    }

    public function __toString()
    {
        return json_encode([
            get_class_name($this),
            $this->broadcastOn()->name,
            $this,
        ]);
    }
}
