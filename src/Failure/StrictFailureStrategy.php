<?php

declare(strict_types=1);

namespace Vista\Logger\Failure;

use RuntimeException;

/**
 * Throws a RuntimeException when a failure occurs.
 */
final class StrictFailureStrategy implements FailureStrategy
{
    /**
     * @throws RuntimeException
     */
    public function handleFailure(string $path, string $message): void
    {
        throw new RuntimeException("Failed to write to path: {$path}. Error: {$message}");
    }
}
