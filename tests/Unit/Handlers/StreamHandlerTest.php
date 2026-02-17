<?php

declare(strict_types=1);

namespace Tests\Unit\Handlers;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use RuntimeException;
use Vista\Logger\Formatters\FormatterInterface;
use Vista\Logger\Handlers\StreamHandler;
use Vista\Logger\LogRecord;

class StreamHandlerTest extends TestCase
{
    /** @var list<string> */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $this->tempFiles = [];
    }

    private function createTempPath(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'log_');

        if ($path === false) {
            throw new RuntimeException('Failed to create temporary file.');
        }

        $this->tempFiles[] = $path;

        return $path;
    }

    public function testThrowsForInvalidMinimumLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new StreamHandler('/tmp/test.log', 'invalid');
    }

    public function testWritesLogRecordToFile(): void
    {
        $path = $this->createTempPath();

        $handler = new StreamHandler($path);

        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'Test message',
            context: [],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00'),
        );

        $handler->handle($record);

        $contents = file_get_contents($path);

        $this->assertNotFalse($contents);
        $this->assertStringContainsString('[2026-01-01 10:00:00]', $contents);
        $this->assertStringContainsString('info: Test message', $contents);
    }

    public function testAppendsToExistingFile(): void
    {
        $path = $this->createTempPath();

        $handler = new StreamHandler($path);

        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'First',
            context: [],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00'),
        );

        $handler->handle($record);
        $handler->handle($record);

        $contents = file_get_contents($path);

        $this->assertNotFalse($contents);
        $this->assertSame(2, substr_count($contents, 'First'));
    }

    public function testDoesNotWriteWhenBelowMinimumLevel(): void
    {
        $path = $this->createTempPath();

        $handler = new StreamHandler($path, LogLevel::ERROR);

        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'Should not be logged',
            context: [],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00'),
        );

        $handler->handle($record);

        $contents = file_get_contents($path);

        $this->assertNotFalse($contents);
        $this->assertSame('', $contents);
    }

    public function testUsesFormatterOutput(): void
    {
        $path = $this->createTempPath();

        $formatter = $this->createMock(FormatterInterface::class);
        $formatter->expects($this->once())
            ->method('format')
            ->willReturn("CUSTOM");

        $handler = new StreamHandler($path, LogLevel::DEBUG, $formatter);

        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'Ignored',
            context: [],
            datetime: new DateTimeImmutable()
        );

        $handler->handle($record);

        $contents = file_get_contents($path);

        $this->assertNotFalse($contents);
        $this->assertSame("CUSTOM", $contents);
    }

    public function testWritesContextAsJson(): void
    {
        $path = $this->createTempPath();

        $handler = new StreamHandler($path);

        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'User logged in',
            context: ['user' => 'John'],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00'),
        );

        $handler->handle($record);

        $contents = file_get_contents($path);

        $this->assertNotFalse($contents);
        $this->assertStringContainsString('{"user":"John"}', $contents);
    }

    public function testDoesNotThrowWhenWriteFailsInNonStrictMode(): void
    {
        $path = '/vista/logger/non_existent_directory/log.txt';

        $handler = new StreamHandler($path);

        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'Failure',
            context: [],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00'),
        );

        set_error_handler(static fn () => true);

        try {
            $handler->handle($record);
            $this->addToAssertionCount(1); // ensure test is not marked as risky
        } finally {
            restore_error_handler();
        }
    }

    public function testThrowsExceptionWhenWriteFailsInStrictMode(): void
    {
        $this->expectException(RuntimeException::class);

        $path = '/vista/logger/non_existent_directory/log.txt';

        $handler = new StreamHandler($path, LogLevel::DEBUG, new \Vista\Logger\Formatters\LineFormatter(), true);

        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'Failure',
            context: [],
            datetime: new DateTimeImmutable('2026-01-01 10:00:00'),
        );

        set_error_handler(static fn () => true);

        try {
            $handler->handle($record);
        } finally {
            restore_error_handler();
        }
    }
}
