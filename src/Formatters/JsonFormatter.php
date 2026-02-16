<?php

declare(strict_types=1);

namespace Vista\Logger\Formatters;

use DateTimeInterface;
use JsonException;
use Vista\Logger\LogRecord;

/**
 * Formats log records as a single-line JSON object.
 *
 * Output format:
 * {
 *   "timestamp": ISO 8601 string,
 *   "level": string,
 *   "message": string,
 *   "context": array
 * }
 *
 * Each record is terminated with a newline for stream-friendly logging.
 */
final class JsonFormatter implements FormatterInterface
{
    /**
     * Formats the given log record as JSON.
     *
     * @throws JsonException If the record cannot be encoded to JSON
     */
    public function format(LogRecord $record): string
    {
        return json_encode([
            'timestamp' => $record->datetime->format(DateTimeInterface::ATOM),
            'level' => $record->level,
            'message' => $record->message,
            'context' => $record->context,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
    }
}
