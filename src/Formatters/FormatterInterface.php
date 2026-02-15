<?php

declare(strict_types=1);

namespace Vista\Logger\Formatters;

use Vista\Logger\LogRecord;

interface FormatterInterface
{
    /**
     * Formats a log record into a string.
     *
     * @param LogRecord $record The log record to format
     * @return string The formatted log line
     */
    public function format(LogRecord $record): string;
}