<?php

namespace GemaDigital\Events;

trait SerializesEvents
{
    /**
     * @return array<string, mixed>
     */
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
        return json_encode($this->toArray()) ?: '[]';
    }
}
