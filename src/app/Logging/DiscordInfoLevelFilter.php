<?php

namespace GemaDigital\Logging;

use Illuminate\Log\Logger;
use Monolog\Handler\FilterHandler;
use Monolog\Level;

class DiscordInfoLevelFilter
{
    public function __invoke(Logger $logger): void
    {
        /** @phpstan-ignore-next-line */
        $handlers = $logger->getHandlers();

        $filtered = array_map(
            fn ($handler) => new FilterHandler($handler, Level::Debug, Level::Info),
            $handlers
        );

        /** @phpstan-ignore-next-line */
        $logger->setHandlers($filtered);
    }
}
