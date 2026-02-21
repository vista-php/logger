<?php

declare(strict_types=1);

namespace Vista\Logger\Handlers;

use Vista\Logger\LogRecord;

/**
 * Handler that discards all log records.
 */
final class NullHandler implements HandlerInterface
{
    public function handle(LogRecord $record): void
    {
        // Do nothing
    }
}
