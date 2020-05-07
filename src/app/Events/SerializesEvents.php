<?php

namespace GemaDigital\Framework\app\Events;

trait SerializesEvents
{
    public function toArray()
    {
        return [
            'data' => (array) $this,
            'event' => $this->broadcastAs(),
            'socket' => null,
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
