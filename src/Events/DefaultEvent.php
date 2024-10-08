<?php

namespace GemaDigital\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use ReflectionClass;

abstract class DefaultEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesEvents;
    use SerializesModels;

    public array $channels;

    public ?object $user;

    public Carbon $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(string|array $channels)
    {
        $this->channels = Arr::wrap($channels);
        $this->user = $this->getUserData();
        $this->timestamp = Carbon::now();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastAs(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return array_map(fn (string $channel): Channel => new Channel($channel), $this->channels);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * Get the user data.
     */
    public function getUserData(): ?object
    {
        $user = Auth::user();

        return $user ? (object) $user->only(['id', 'name', 'email']) : null;
    }

    /**
     * Format the event for broadcast.
     */
    public function __toString(): string
    {
        $broadcastOn = $this->broadcastOn();

        return (string) json_encode([
            get_class_name($this),
            count($broadcastOn) === 1 ? $broadcastOn[0] : $broadcastOn,
            $this,
        ]);
    }
}
