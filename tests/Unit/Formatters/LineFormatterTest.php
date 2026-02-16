<?php

declare(strict_types=1);

namespace Tests\Unit\Formatters;

use PHPUnit\Framework\TestCase;
use Vista\Logger\Formatters\LineFormatter;
use Vista\Logger\LogRecord;
use Psr\Log\LogLevel;
use DateTimeImmutable;
use JsonException;

final class LineFormatterTest extends TestCase
{
    private LineFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new LineFormatter();
    }

    public function testFormatsBasicRecord(): void
    {
        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'Test message',
            context: [],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00')
        );

        $result = $this->formatter->format($record);

        $this->assertSame(
            "[2026-01-01 10:00:00] info: Test message\n",
            $result
        );
    }

    public function testFormatsWithContext(): void
    {
        $record = new LogRecord(
            level: LogLevel::ERROR,
            message: 'Failure',
            context: ['key' => 'value'],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00')
        );

        $result = $this->formatter->format($record);

        $this->assertSame(
            "[2026-01-01 10:00:00] error: Failure {\"key\":\"value\"}\n",
            $result
        );
    }

    public function testEndsWithNewline(): void
    {
        $record = new LogRecord(
            level: LogLevel::DEBUG,
            message: 'Line',
            context: [],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00')
        );

        $result = $this->formatter->format($record);

        $this->assertStringEndsWith("\n", $result);
    }

    public function testThrowsJsonExceptionForInvalidContext(): void
    {
        $resource = tmpfile();
        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'Test',
            context: ["invalid" => $resource],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00')
        );

        $this->expectException(JsonException::class);

        try {
            $this->formatter->format($record);
        } finally {
            fclose($resource);
        }
    }

    public function testDoesNotEscapeSlashes(): void
    {
        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'URL',
            context: ['url' => 'https://example.com'],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00')
        );

        $result = $this->formatter->format($record);

        $this->assertStringContainsString(
            '"https://example.com"',
            $result
        );
    }
}
