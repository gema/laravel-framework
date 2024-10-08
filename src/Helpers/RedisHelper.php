<?php

namespace GemaDigital\Helpers;

use GemaDigital\Events\DefaultEvent;
use Illuminate\Support\Facades\Redis;

trait RedisHelper
{
    public function redisPublish(DefaultEvent $event): array
    {
        $redis = Redis::connection();
        $status = $redis->publish($event->getChannel(), $event->toJson());

        return [
            'status' => (bool) $status,
            'event' => $event->toArray(),
        ];
    }
}
