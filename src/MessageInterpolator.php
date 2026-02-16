<?php

declare(strict_types=1);

namespace Vista\Logger;

use Stringable;

/**
 * Performs PSR-3 style message interpolation.
 *
 * Replaces `{key}` placeholders in log messages with scalar or
 * Stringable values from the provided context array.
 */
final class MessageInterpolator
{
    /**
     * Replaces `{key}` placeholders in the message with scalar or Stringable values from context.
     *
     * @param array<string, mixed> $context
     */
    public function interpolate(string $message, array $context): string
    {
        if ($context === []) {
            return $message;
        }

        /** @var array<string, string> $replace */
        $replace = [];

        foreach ($context as $key => $value) {
            if (is_scalar($value) || $value instanceof Stringable) {
                $replace['{' . $key . '}'] = (string) $value;
            }
        }

        return $replace === [] ? $message : strtr($message, $replace);
    }
}
