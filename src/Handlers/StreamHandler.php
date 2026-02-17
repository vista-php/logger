<?php

declare(strict_types=1);

namespace Vista\Logger\Handlers;

use InvalidArgumentException;
use Psr\Log\LogLevel;
use RuntimeException;
use Vista\Logger\Contracts\HandlerInterface;
use Vista\Logger\Formatters\FormatterInterface;
use Vista\Logger\Formatters\LineFormatter;
use Vista\Logger\LevelMap;
use Vista\Logger\LogRecord;

/**
 * Writes log records to a file or stream.
 *
 * Filters records below the configured minimum log level
 * and appends the formatted output provided by the configured formatter.
 */
final class StreamHandler implements HandlerInterface
{
    private int $minLevelPriority;

    /**
     * @param string             $path      File path or stream URI (e.g. php://stdout)
     * @param string             $minLevel  Minimum PSR-3 log level to be written
     * @param FormatterInterface $formatter Formatter used to serialize log records before writing.
     *                                      Defaults to LineFormatter.
     * @param bool               $strict    If true, exceptions will be thrown on write failures.
     *                                      Otherwise, errors will be logged to the PHP error log.
     *
     * @throws InvalidArgumentException If the minimum level is invalid
     */
    public function __construct(
        private readonly string $path,
        string $minLevel = LogLevel::DEBUG,
        private readonly FormatterInterface $formatter = new LineFormatter(),
        private readonly bool $strict = false,
    ) {
        $this->minLevelPriority = LevelMap::toPriority($minLevel);
    }

    /**
     * Writes the given log record if it meets the minimum level.
     *
     * @throws RuntimeException If writing to the stream fails
     */
    public function handle(LogRecord $record): void
    {
        if (LevelMap::toPriority($record->level) < $this->minLevelPriority) {
            return;
        }

        $line = $this->formatter->format($record);

        $this->writeLine($line);
    }

    /**
     * @throws RuntimeException If writing to the stream fails
     */
    private function writeLine(string $line): void
    {
        $result = file_put_contents($this->path, $line, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            $message = sprintf('Failed to write log to %s: %s', $this->path, $this->errorMessage());

            if ($this->strict) {
                throw new RuntimeException($message);
            }

            error_log($message);
        }
    }

    private function errorMessage(): string
    {
        return error_get_last()['message'] ?? 'unknown error';
    }
}
