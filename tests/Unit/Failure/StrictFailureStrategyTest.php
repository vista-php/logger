<?php

declare(strict_types=1);

namespace Tests\Unit\Failure;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vista\Logger\Failure\StrictFailureStrategy;

class StrictFailureStrategyTest extends TestCase
{
    public function testHandleFailureThrowsException(): void
    {
        $strategy = new StrictFailureStrategy();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test message');

        $strategy->handleFailure('Test message');
    }
}
