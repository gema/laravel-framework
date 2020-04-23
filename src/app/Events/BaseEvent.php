<?php

namespace GemaDigital\Framework\App\Events;

use Auth;
use Carbon\Carbon;
use GemaDigital\Framework\App\Events\SerializesEvents;
use GemaDigital\Framework\App\Events\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BaseEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, SerializesEvents;

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

    public function getChannel()
    {
        return $this->channel;
    }

    public function getUserData()
    {
        return (object) Auth::user()->only(['id', 'name', 'email']) ?? null;
    }
}
