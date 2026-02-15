<?php

namespace Tests\Unit\Handlers;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Vista\Logger\Handlers\StreamHandler;
use Vista\Logger\LogRecord;

class StreamHandlerTest extends TestCase
{
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
        $this->tempFiles[] = $path;

        return $path;
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
}
