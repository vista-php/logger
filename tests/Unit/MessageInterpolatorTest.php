<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vista\Logger\MessageInterpolator;

class MessageInterpolatorTest extends TestCase
{
    private function interpolator(): MessageInterpolator
    {
        return new MessageInterpolator();
    }

    public function testReplacesAllMatchingPlaceholders(): void
    {
        $message = 'User {username} created a new post with id {postId}';
        $context = ['username' => 'john_doe', 'postId' => 123];
        $expected = 'User john_doe created a new post with id 123';

        $this->assertSame($expected, $this->interpolator()->interpolate($message, $context));
    }

    public function testAllOccurrencesOfPlaceholderAreReplaced(): void
    {
        $message = '{name} likes {name}\'s code';
        $context = ['name' => 'John'];
        $expected = 'John likes John\'s code';

        $this->assertSame($expected, $this->interpolator()->interpolate($message, $context));
    }

    public function testBooleanAndIntegerValuesAreReplaced(): void
    {
        $message = 'Active: {active}, Count: {count}';
        $context = ['active' => true, 'count' => 5];
        $expected = 'Active: 1, Count: 5';

        $this->assertSame($expected, $this->interpolator()->interpolate($message, $context));
    }

    public function testStringableValuesAreReplaced(): void
    {
        $message = 'Hello {name}';
        $context = ['name' => new class () {
            public function __toString(): string
            {
                return 'Alice';
            }
        }];
        $expected = 'Hello Alice';

        $this->assertSame($expected, $this->interpolator()->interpolate($message, $context));
    }

    public function testNonScalarContextValuesAreIgnored(): void
    {
        $message = 'User {username} created a new post with id {postId}';
        $context = ['username' => 'john_doe', 'postId' => ['id' => 123]];
        $expected = 'User john_doe created a new post with id {postId}';

        $this->assertSame($expected, $this->interpolator()->interpolate($message, $context));
    }

    public function testNullContextValuesAreIgnored(): void
    {
        $message = 'Value is {key}';
        $context = ['key' => null];
        $expected = 'Value is {key}';

        $this->assertSame($expected, $this->interpolator()->interpolate($message, $context));
    }

    public function testPlaceholderRemainsWhenContextKeyIsMissing(): void
    {
        $message = 'User {username} created a new post with id {postId}';
        $context = ['username' => 'john_doe'];
        $expected = 'User john_doe created a new post with id {postId}';

        $this->assertSame($expected, $this->interpolator()->interpolate($message, $context));
    }

    public function testMessageIsUnchangedWhenContextIsEmpty(): void
    {
        $message = 'Value is {key}';
        $context = [];
        $expected = 'Value is {key}';

        $this->assertSame($expected, $this->interpolator()->interpolate($message, $context));
    }

    public function testUnusedContextKeysDoNotAffectMessage(): void
    {
        $message = 'Hello world';
        $context = ['unused' => 'value'];
        $expected = 'Hello world';

        $this->assertSame($expected, $this->interpolator()->interpolate($message, $context));
    }

    public function testPlaceholdersWithoutMatchingBracesAreNotReplaced(): void
    {
        $message = 'This is {valid} but this is invalid} and {also_invalid';
        $context = ['valid' => 'correct'];
        $expected = 'This is correct but this is invalid} and {also_invalid';

        $this->assertSame($expected, $this->interpolator()->interpolate($message, $context));
    }
}
