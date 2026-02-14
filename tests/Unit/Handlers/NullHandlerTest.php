<?php

declare(strict_types=1);

namespace Tests\Unit\Handlers;

use PHPUnit\Framework\TestCase;
use Vista\Logger\Handlers\NullHandler;
use Vista\Logger\LogRecord;
use Psr\Log\LogLevel;
use DateTimeImmutable;

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