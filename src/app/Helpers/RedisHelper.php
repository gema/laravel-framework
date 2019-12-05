<?php

namespace GemaDigital\Framework\App\Helpers;

use GemaDigital\Framework\App\Events\BaseEvent;
use Illuminate\Support\Facades\Redis;

trait RedisHelper
{
    public function redisPublish(BaseEvent $event)
    {
        $redis = Redis::connection();
        $status = $redis->publish($event->getChannel(), $event->toJson());

        return [
            'status' => (bool) $status,
            'event' => $event->toArray(),
        ];
    }

}
