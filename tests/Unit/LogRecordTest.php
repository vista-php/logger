<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Vista\Logger\LogRecord;

class LogRecordTest extends TestCase
{
    public function testLogRecordStoresValues(): void
    {
        $now = new DateTimeImmutable('2026-02-10 00:00:00');
        $record = new LogRecord(
            level: LogLevel::INFO,
            message: 'Test {key}',
            context: ['key' => 'value'],
            datetime: $now
        );

        $this->assertSame(LogLevel::INFO, $record->level);
        $this->assertSame('Test {key}', $record->message);
        $this->assertSame(['key' => 'value'], $record->context);
        $this->assertSame($now, $record->datetime);
    }
}
