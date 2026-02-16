<?php

declare(strict_types=1);

namespace Tests\Unit\Formatters;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use JsonException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Vista\Logger\Formatters\JsonFormatter;
use Vista\Logger\LogRecord;

final class JsonFormatterTest extends TestCase
{
    private JsonFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new JsonFormatter();
    }

    public function testFormatsRecordToValidJson(): void
    {
        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'Test message',
            context: ['key' => 'value'],
            datetime: new DateTimeImmutable(
                '2026-01-01 10:00:00',
                new DateTimeZone('UTC')
            )
        );

        $result = $this->formatter->format($record);

        $this->assertStringEndsWith("\n", $result);

        $decoded = json_decode(trim($result), true);

        $this->assertIsArray($decoded);
        $this->assertSame($record->datetime->format(DateTimeInterface::ATOM), $decoded['timestamp']);
        $this->assertSame($record->level, $decoded['level']);
        $this->assertSame($record->message, $decoded['message']);
        $this->assertSame($record->context, $decoded['context']);
    }

    public function testDoesNotEscapeSlashes(): void
    {
        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'URL',
            context: ['url' => 'https://example.com'],
            datetime: new DateTimeImmutable(
                '2026-01-01 10:00:00',
                new DateTimeZone('UTC')
            )
        );

        $result = $this->formatter->format($record);

        $this->assertIsString($record->context['url']);
        $this->assertStringContainsString(
            '"' . $record->context['url'] . '"',
            $result
        );
    }

    public function testThrowsJsonExceptionForInvalidContext(): void
    {
        $resource = tmpfile();

        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'Test',
            context: ['invalid' => $resource],
            datetime: new DateTimeImmutable(
                '2026-01-01 10:00:00',
                new DateTimeZone('UTC')
            )
        );

        $this->expectException(JsonException::class);

        try {
            $this->formatter->format($record);
        } finally {
            fclose($resource);
        }
    }

    public function testFormatsWithEmptyContext(): void
    {
        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'No context',
            context: [],
            datetime: new DateTimeImmutable(
                '2026-01-01 10:00:00',
                new DateTimeZone('UTC')
            )
        );

        $result = $this->formatter->format($record);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode(trim($result), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame([], $decoded['context']);
    }
}
