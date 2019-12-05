<?php

namespace GemaDigital\Framework\App\Events;

trait SerializesEvents
{
    public function toArray()
    {
        return array_merge((array) $this, ['event' => $this->broadcastAs()]);
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
