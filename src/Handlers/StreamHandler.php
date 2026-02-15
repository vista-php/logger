<?php

declare(strict_types=1);

namespace Vista\Logger\Handlers;

use InvalidArgumentException;
use Psr\Log\LogLevel;
use Vista\Logger\Contracts\HandlerInterface;
use Vista\Logger\LevelMap;
use Vista\Logger\LogRecord;

/**
 * Writes log records to a file or stream.
 *
 * Filters records below the configured minimum log level
 * and appends formatted log lines to the given path.
 *
 * The output format is:
 * [Y-m-d H:i:s] level: message {context as JSON}
 */
final class StreamHandler implements HandlerInterface
{
    private int $minLevelPriority;

    /**
     * @param string $path      File path or stream URI (e.g. php://stdout)
     * @param string $minLevel  Minimum PSR-3 log level to be written
     *
     * @throws InvalidArgumentException If the minimum level is invalid
     */
    public function __construct(
        private readonly string $path,
        string $minLevel = LogLevel::DEBUG
    ) {
        $this->minLevelPriority = LevelMap::toPriority($minLevel);
    }

    /**
     * Writes the given log record if it meets the minimum level.
     */
    public function handle(LogRecord $record): void
    {
        if (LevelMap::toPriority($record->level) < $this->minLevelPriority) {
            return;
        }

        $line = sprintf(
            "[%s] %s: %s %s\n",
            $record->datetime->format('Y-m-d H:i:s'),
            $record->level,
            $record->message,
            empty($record->context) ? '' : json_encode($record->context)
        );

        file_put_contents($this->path, $line, FILE_APPEND);
    }
}
