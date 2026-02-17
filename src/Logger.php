<?php

declare(strict_types=1);

namespace Vista\Logger;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;
use Vista\Logger\Contracts\HandlerInterface;

/**
 * PSR-3 compliant logger implementation.
 *
 * Validates log levels, interpolates messages, and dispatches
 * immutable LogRecord instances to registered handlers.
 */
final class Logger implements LoggerInterface
{
    /** @var HandlerInterface[] */
    private array $handlers;

    private MessageInterpolator $interpolator;

    public function __construct(HandlerInterface ...$handlers)
    {
        $this->handlers = $handlers;
        $this->interpolator = new MessageInterpolator();
    }

    /**
     * @param array<string, mixed> $context
     */
    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * @param string $level PSR-3 log level
     * @param array<string, mixed> $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        LevelMap::assertValid($level);

        if ($this->handlers === []) {
            return;
        }

        $record = $this->createRecord($level, $message, $context);

        foreach ($this->handlers as $handler) {
            $handler->handle($record);
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private function createRecord(string $level, string|Stringable $message, array $context): LogRecord
    {
        return new LogRecord(
            level: $level,
            message: $this->interpolator->interpolate((string) $message, $context),
            context: $context,
            datetime: new DateTimeImmutable(),
        );
    }
}
