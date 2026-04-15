<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FormattableHandlerInterface;

class CustomizeLogFormatter
{
    public function __invoke(Logger $logger): void
    {
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message%\nContext: %context%\nExtra: %extra%\n",
            'Y-m-d H:i:s',
            true,
            true,
            true
        );

        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof FormattableHandlerInterface) {
                $handler->setFormatter($formatter);
            }
        }
    }
}
