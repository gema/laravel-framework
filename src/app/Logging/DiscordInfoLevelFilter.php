<?php

namespace GemaDigital\Logging;

use Monolog\Handler\FilterHandler;
use Monolog\Level;
use Monolog\Logger;

class DiscordInfoLevelFilter
{
    public function __invoke(Logger $logger): void
    {
        $handlers = $logger->getHandlers();

        $filtered = array_map(
            fn ($handler) => new FilterHandler($handler, Level::Debug, Level::Info),
            $handlers
        );

        $logger->setHandlers($filtered);
    }
}
