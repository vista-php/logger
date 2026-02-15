<?php

declare(strict_types=1);

namespace Vista\Logger\Formatters;

use Vista\Logger\LogRecord;

/**
 * Formats log records into a single-line human-readable string.
 *
 * Output format:
 * [Y-m-d H:i:s] level: message {context as JSON}
 *
 * Context is appended as JSON only when not empty.
 */
final class LineFormatter implements FormatterInterface
{
    /**
     * Formats the given log record into a single-line string.
     */
    public function format(LogRecord $record): string
    {
        return sprintf(
            "[%s] %s: %s%s\n",
            $record->datetime->format('Y-m-d H:i:s'),
            $record->level,
            $record->message,
            $this->formatContext($record->context)
        );
    }

    private function formatContext(array $context): string
    {
        return empty($context)
            ? ''
            : ' ' . json_encode($context);
    }
}