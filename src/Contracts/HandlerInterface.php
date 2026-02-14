<?php

declare(strict_types=1);

namespace Vista\Logger\Contracts;

use Vista\Logger\LogRecord;

interface HandlerInterface
{
    public function handle(LogRecord $record): void;
}
