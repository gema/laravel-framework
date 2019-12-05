<?php

namespace GemaDigital\Framework\App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast as BaseShouldBroadcast;

interface ShouldBroadcast extends BaseShouldBroadcast
{
    public function getChannel();
}
