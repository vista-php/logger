<?php

declare(strict_types=1);

namespace Tests\Unit\Formatters;

use PHPUnit\Framework\TestCase;
use Vista\Logger\Formatters\LineFormatter;
use Vista\Logger\LogRecord;
use Psr\Log\LogLevel;
use DateTimeImmutable;

final class LineFormatterTest extends TestCase
{
    public function testFormatsBasicRecord(): void
    {
        $formatter = new LineFormatter();

        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'Test message',
            context: [],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00')
        );

        $result = $formatter->format($record);

        $this->assertSame(
            "[2026-01-01 10:00:00] info: Test message\n",
            $result
        );
    }

    public function testFormatsWithContext(): void
    {
        $formatter = new LineFormatter();

        $record = new LogRecord(
            level: LogLevel::ERROR,
            message: 'Failure',
            context: ['key' => 'value'],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00')
        );

        $result = $formatter->format($record);

        $this->assertSame(
            "[2026-01-01 10:00:00] error: Failure {\"key\":\"value\"}\n",
            $result
        );
    }

    public function testEndsWithNewline(): void
    {
        $formatter = new LineFormatter();

        $record = new LogRecord(
            level: LogLevel::DEBUG,
            message: 'Line',
            context: [],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00')
        );

        $result = $formatter->format($record);

        $this->assertStringEndsWith("\n", $result);
    }
}