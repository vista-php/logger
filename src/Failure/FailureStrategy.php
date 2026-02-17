<?php

declare(strict_types=1);

namespace Vista\Logger\Failure;

/**
 * Defines how write failures are handled.
 */
interface FailureStrategy
{
    /**
     * Handles a failure condition.
     *
     * @param string $message A descriptive failure message.
     */
    public function handleFailure(string $message): void;
}
