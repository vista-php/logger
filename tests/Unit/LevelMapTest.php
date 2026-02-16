<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Vista\Logger\LevelMap;

class LevelMapTest extends TestCase
{
    #[TestWith([100, LogLevel::DEBUG])]
    #[TestWith([200, LogLevel::INFO])]
    #[TestWith([250, LogLevel::NOTICE])]
    #[TestWith([300, LogLevel::WARNING])]
    #[TestWith([400, LogLevel::ERROR])]
    #[TestWith([500, LogLevel::CRITICAL])]
    #[TestWith([550, LogLevel::ALERT])]
    #[TestWith([600, LogLevel::EMERGENCY])]
    public function testToPriority(int $level, string $logLevel): void
    {
        $this->assertSame($level, LevelMap::toPriority($logLevel));
    }

    public function testToPriorityThrowsForInvalidLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        LevelMap::toPriority('invalid');
    }

    #[TestWith([LogLevel::DEBUG])]
    #[TestWith([LogLevel::INFO])]
    #[TestWith([LogLevel::NOTICE])]
    #[TestWith([LogLevel::WARNING])]
    #[TestWith([LogLevel::ERROR])]
    #[TestWith([LogLevel::CRITICAL])]
    #[TestWith([LogLevel::ALERT])]
    #[TestWith([LogLevel::EMERGENCY])]
    public function testAssertValidDoesNotThrowForValidLevels(string $logLevel): void
    {
        $this->expectNotToPerformAssertions();
        LevelMap::assertValid($logLevel);
    }

    public function testAssertInvalidLogLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        LevelMap::assertValid('invalid');
    }
}
