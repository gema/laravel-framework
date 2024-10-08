<?php

namespace GemaDigital\Events;

trait SerializesEvents
{
    public function toArray(): array
    {
        return [
            'data' => (array) $this,
            'event' => $this->broadcastAs(),
            'socket' => null,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
