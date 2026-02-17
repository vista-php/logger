<?php

declare(strict_types=1);

namespace Vista\Logger\Failure;

/**
 * Logs failures using PHP's error_log.
 */
final class ErrorLogFailureStrategy implements FailureStrategy
{
    public function handleFailure(string $message): void
    {
        error_log($message);
    }
}
