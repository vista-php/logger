<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Vista\Logger\LevelFilter;
use Vista\Logger\LevelMap;

final class LevelFilterTest extends TestCase
{
    public function testAllowsForAllLevelCombinations(): void
    {
        $levels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        ];

        foreach ($levels as $minLevel) {
            $filter = new LevelFilter($minLevel);

            foreach ($levels as $givenLevel) {
                $expected = LevelMap::toPriority($givenLevel) >= LevelMap::toPriority($minLevel);

                $this->assertSame($expected, $filter->allows($givenLevel));
            }
        }
    }

    public function testAllowsThrowsForInvalidLevel(): void
    {
        $filter = new LevelFilter(LogLevel::DEBUG);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid log level: invalid');

        $filter->allows('invalid');
    }

    public function testConstructorThrowsForInvalidMinimumLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid log level: invalid');

        new LevelFilter('invalid');
    }
}
