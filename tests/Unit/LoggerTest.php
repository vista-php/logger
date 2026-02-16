<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Vista\Logger\Contracts\HandlerInterface;
use Vista\Logger\Logger;
use Vista\Logger\LogRecord;
use Vista\Logger\MessageInterpolator;

final class LoggerTest extends TestCase
{
    /**
     * @return array{0: Logger, 1: HandlerInterface&MockObject}
     */
    private function createLoggerWithHandler(): array
    {
        $handler = $this->createMock(HandlerInterface::class);
        $logger = new Logger(new MessageInterpolator(), $handler);

        return [$logger, $handler];
    }

    #[TestWith(['emergency'])]
    #[TestWith(['alert'])]
    #[TestWith(['critical'])]
    #[TestWith(['warning'])]
    #[TestWith(['notice'])]
    #[TestWith(['error'])]
    #[TestWith(['info'])]
    #[TestWith(['debug'])]
    public function testMethodCallsHandler(string $method): void
    {
        [$logger, $handler] = $this->createLoggerWithHandler();
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(LogRecord::class));

        $logger->{$method}('Test message');
    }

    #[TestWith(['emergency', LogLevel::EMERGENCY])]
    #[TestWith(['alert', LogLevel::ALERT])]
    #[TestWith(['critical', LogLevel::CRITICAL])]
    #[TestWith(['error', LogLevel::ERROR])]
    #[TestWith(['warning', LogLevel::WARNING])]
    #[TestWith(['notice', LogLevel::NOTICE])]
    #[TestWith(['info', LogLevel::INFO])]
    #[TestWith(['debug', LogLevel::DEBUG])]
    public function testMethodForwardsCorrectLevel(string $method, string $expectedLevel): void
    {
        [$logger, $handler] = $this->createLoggerWithHandler();
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(
                fn (LogRecord $record) => $record->level === $expectedLevel
            ));

        $logger->{$method}('Test');
    }

    public function testLogInterpolatesMessage(): void
    {
        [$logger, $handler] = $this->createLoggerWithHandler();
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(
                fn (LogRecord $record) => $record->message === 'Hello John'
            ));

        $logger->info('Hello {name}', ['name' => 'John']);
    }

    public function testLogDoesNothingWhenNoHandlersAreRegistered(): void
    {
        $this->expectNotToPerformAssertions();

        $logger = new Logger(new MessageInterpolator());
        $logger->info('Test');
    }

    public function testLogThrowsForInvalidLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid log level: invalid');

        $logger = new Logger(new MessageInterpolator());
        $logger->log('invalid', 'Message');
    }
}
