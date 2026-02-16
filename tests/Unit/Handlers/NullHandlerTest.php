<?php

declare(strict_types=1);

namespace Tests\Unit\Handlers;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Vista\Logger\Handlers\NullHandler;
use Vista\Logger\LogRecord;

final class NullHandlerTest extends TestCase
{
    public function testHandleDoesNothing(): void
    {
        $this->expectNotToPerformAssertions();

        $handler = new NullHandler();

        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'Test',
            context: ['key' => 'value'],
            datetime: new DateTimeImmutable('2026-01-01 00:00:00'),
        );

        $handler->handle($record);
    }
}
