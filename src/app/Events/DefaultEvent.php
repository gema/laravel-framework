<?php

namespace GemaDigital\Framework\app\Events;

use Carbon\Carbon;
use GemaDigital\Framework\app\Events\SerializesEvents;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

abstract class DefaultEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    use SerializesEvents;

    public string $channel;
    public ?Authenticatable $user;
    public Carbon $timestamp;

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

    public function broadcastAs(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function broadcastOn(): Channel
    {
        return new Channel($this->channel);
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getUserData(): ?Authenticatable
    {
        $user = Auth::user();

        return $user ? (object) $user->only(['id', 'name', 'email']) : null;
    }

    public function __toString(): string
    {
        return json_encode([
            get_class_name($this),
            $this->broadcastOn()->name,
            $this,
        ]);
    }
}
