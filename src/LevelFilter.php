<?php

declare(strict_types=1);

namespace Vista\Logger;

use InvalidArgumentException;

/**
 * Filters log records based on their level.
 */
final class LevelFilter
{
    private int $minPriority;

    /**
     * @throws InvalidArgumentException If the minimum level is invalid.
     */
    public function __construct(string $minLevel)
    {
        $this->minPriority = LevelMap::toPriority($minLevel);
    }

    /**
     * Determines if the given log record meets the minimum level requirement.
     *
     * @throws InvalidArgumentException If the log level is invalid.
     */
    public function allows(string $level): bool
    {
        return LevelMap::toPriority($level) >= $this->minPriority;
    }
}
