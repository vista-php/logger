<?php

declare(strict_types=1);

namespace Tests\Unit\Failure;

use PHPUnit\Framework\TestCase;
use Vista\Logger\Failure\ErrorLogFailureStrategy;

final class ErrorLogFailureStrategyTest extends TestCase
{
    public function testHandleFailureDoesNotThrowException(): void
    {
        $strategy = new ErrorLogFailureStrategy();

        $this->expectNotToPerformAssertions();

        $strategy->handleFailure('/tmp/test.log', 'Test message');
    }
}
