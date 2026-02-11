<?php

declare(strict_types=1);

namespace Vista\Logger;

use InvalidArgumentException;
use Psr\Log\LogLevel;

final class LevelMap
{
    /**
     * Converts a log level string to its corresponding priority integer.
     *
     * @throws InvalidArgumentException if the level is not valid
     */
    public static function toPriority(string $level): int
    {
        return match ($level) {
            LogLevel::EMERGENCY => 600,
            LogLevel::ALERT => 550,
            LogLevel::CRITICAL => 500,
            LogLevel::ERROR => 400,
            LogLevel::WARNING => 300,
            LogLevel::NOTICE => 250,
            LogLevel::INFO => 200,
            LogLevel::DEBUG => 100,
            default => throw new InvalidArgumentException("Invalid log level: $level"),
        };
    }

    /**
     * Asserts that a log level is valid.
     *
     * @throws InvalidArgumentException if the level is not valid
     */
    public static function assertValid(string $level): void
    {
        self::toPriority($level);
    }
}