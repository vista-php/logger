<?php

declare(strict_types=1);

namespace Vista\Logger\Handlers;

use InvalidArgumentException;
use Psr\Log\LogLevel;
use RuntimeException;
use Vista\Logger\Contracts\HandlerInterface;
use Vista\Logger\Failure\ErrorLogFailureStrategy;
use Vista\Logger\Failure\FailureStrategy;
use Vista\Logger\Formatters\FormatterInterface;
use Vista\Logger\Formatters\LineFormatter;
use Vista\Logger\LevelFilter;
use Vista\Logger\LogRecord;

/**
 * Writes log records to a file or stream.
 *
 * Filters records below the configured minimum log level
 * and appends the formatted output provided by the configured formatter.
 */
final class StreamHandler implements HandlerInterface
{
    private LevelFilter $levelFilter;

    /**
     * @param string             $path            File path or stream URI (e.g. php://stdout)
     * @param string             $minLevel        Minimum PSR-3 log level to be written
     * @param FormatterInterface $formatter       Formatter used to serialize log records before writing.
     *                                            Defaults to LineFormatter.
     * @param FailureStrategy    $failureStrategy The failure strategy to use when writing fails.
     *
     * @throws InvalidArgumentException If the minimum level is invalid
     */
    public function __construct(
        private readonly string $path,
        string $minLevel = LogLevel::DEBUG,
        private readonly FormatterInterface $formatter = new LineFormatter(),
        private readonly FailureStrategy $failureStrategy = new ErrorLogFailureStrategy(),
    ) {
        $this->levelFilter = new LevelFilter($minLevel);
    }

    /**
     * Writes the given log record if it meets the minimum level.
     *
     * @throws RuntimeException If writing to the stream fails
     */
    public function handle(LogRecord $record): void
    {
        if (!$this->levelFilter->allows($record->level)) {
            return;
        }

        $this->write($this->formatter->format($record));
    }

    /**
     * @throws RuntimeException If the configured failure strategy escalates the failure
     */
    private function write(string $formatted): void
    {
        $result = file_put_contents($this->path, $formatted, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            $error = error_get_last()['message'] ?? 'unknown error';

            $this->failureStrategy->handleFailure($this->path, $error);
        }
    }
}
