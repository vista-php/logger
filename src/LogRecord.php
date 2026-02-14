<?php

declare(strict_types=1);

namespace Vista\Logger;

use DateTimeImmutable;

/**
 * Immutable value object representing a single log entry.
 *
 * Contains the log level, interpolated message, context data,
 * and the timestamp at which the record was created.
 */
final class LogRecord
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly string $level,
        public readonly string $message,
        public readonly array $context,
        public readonly DateTimeImmutable $datetime,
    ) {}
}