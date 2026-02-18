# vista-php/logger

![Latest Version](https://img.shields.io/packagist/v/vista-php/logger)
![PHP Version](https://img.shields.io/packagist/php-v/vista-php/logger)
![License](https://img.shields.io/packagist/l/vista-php/logger)
![CI](https://github.com/vista-php/logger/actions/workflows/ci.yml/badge.svg)

PSR-3 compliant logging package for PHP 8.3+.

Designed for clean architecture, strict correctness, and framework-quality maintainability.

No hidden state. No speculative abstractions. Explicit, configurable failure semantics.

---
## Table of Contents

- [Philosophy](#philosophy)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Architecture Overview](#architecture-overview)
  - [Core Principles](#core-principles)
- [Basic Usage](#basic-usage)
- [Log Levels](#log-levels)
- [LogRecord](#logrecord)
- [Multiple Handlers](#multiple-handlers)
  - [Execution Semantics](#execution-semantics)
- [Handlers](#handlers)
  - [StreamHandler](#streamhandler)
    - [Custom Formatter](#custom-formatter)
    - [Failure Strategies](#failure-strategies)
  - [NullHandler](#nullhandler)
- [Formatters](#formatters)
  - [LineFormatter](#lineformatter-default)
  - [JsonFormatter](#jsonformatter)
- [Message Interpolation](#message-interpolation)
- [Failure Semantics](#failure-semantics)
- [Design Decisions](#design-decisions)
- [Testing Philosophy](#testing-philosophy)
- [Versioning](#versioning)
- [Why not Monolog?](#why-not-monolog)
- [License](#license)
- [Contributing](#contributing)
  - [Non-Goals](#non-goals)
  - [Development Setup](#development-setup)

---

## Philosophy

`vista-php/logger` is a minimal, infrastructure-level logging foundation.

It strictly implements the PSR-3 contract while enforcing clear architectural boundaries:
- Validation occurs at the API boundary.
- Log records are immutable and treated as data carriers.
- Handlers encapsulate output policy.
- Formatters encapsulate serialization.
- Failure behavior is explicit and configurable.

The package prioritizes clarity, determinism, and explicit behavior over feature breadth.

---

## Features

- Strict PSR-3 compliance
- Immutable `LogRecord` value objects
- Clean SRP-driven architecture
- Explicit log level validation
- Stream-based logging
- Pluggable formatters
- Deterministic, environment-independent behavior
- Fail-fast on programmer errors
- Configurable failure strategies
- Explicit multi-handler execution semantics
- No global state
- No hidden side effects

---

## Requirements

- PHP 8.3+
- psr/log ^3.0

Tested against PHP 8.3, 8.4, and 8.5.

---

## Installation

```bash
composer require vista-php/logger
```

---

## Architecture Overview

The logging pipeline is intentionally simple and explicit:
```
Logger
  → LogRecord (immutable)
    → HandlerInterface
        → FormatterInterface
            → Output (file / stream)
```

### Core Principles

- Strict Single Responsibility (SRP)
- Clear API boundary validation
- Immutable value objects
- Explicit failure semantics
- Fail-fast philosophy
- No speculative extension points
- Production-ready, minimal surface area

---

## Basic Usage

```php
use Vista\Logger\Logger;
use Vista\Logger\Handlers\StreamHandler;
use Psr\Log\LogLevel;

$logger = new Logger(
    new StreamHandler(__DIR__ . '/app.log', LogLevel::INFO)
);

$logger->info('User {name} logged in', ['name' => 'John']);
```

---

## Log Levels

All PSR-3 log levels are supported:
- `emergency`
- `alert`
- `critical`
- `error`
- `warning`
- `notice`
- `info`
- `debug`

Invalid levels throw `InvalidArgumentException` immediately, even if no handlers are registered.

Validation occurs at the `Logger` API boundary.

---

## `LogRecord`

Each log entry is represented by an immutable `LogRecord`:

```php
final class LogRecord
{
    public readonly string $level;
    public readonly string $message;
    public readonly array $context;
    public readonly DateTimeImmutable $datetime;
}
```

Records are created exclusively by the `Logger` and passed to handlers. They are treated as immutable data carriers.

---

## Multiple Handlers

You may register multiple handlers:

```php
$logger = new Logger($handlerA, $handlerB);
```

### Execution Semantics

Handlers are executed sequentially.

If any handler throws an exception:
- Execution stops immediately.
- Subsequent handlers are not executed.
- The exception bubbles up to the caller.

This behavior is intentional and aligns with the fail-fast design philosophy.

--- 
## Handlers

### `StreamHandler`

Writes log records to a file or stream.
- Filters by minimum log level
- Delegates formatting
- Appends output using `FILE_APPEND | LOCK_EX`
- Supports any stream URI (e.g. `php://stdout`)
- Does not manage stream resources manually
- Does not buffer
- Does not rotate files
  ```php
  use Vista\Logger\Handlers\StreamHandler;
  use Psr\Log\LogLevel;

  $handler = new StreamHandler(
      path: 'php://stdout',
      minLevel: LogLevel::WARNING
  );
  ```
> Note: File locking relies on underlying filesystem semantics
> Behavior may vary on certain network filesystems.

#### Custom Formatter

You can provide a custom formatter implementation:

```php
use Vista\Logger\Handlers\StreamHandler;
use Vista\Logger\Formatters\JsonFormatter;
use Psr\Log\LogLevel;

$handler = new StreamHandler(
    path: __DIR__ . '/app.log',
    minLevel: LogLevel::INFO,
    formatter: new JsonFormatter()
);
```

Any implementation of `FormatterInterface` can be injected.

#### Failure Strategies

By default, write failures are reported via `error_log()` and do not interrupt application flow.

You can use `StrictFailureStrategy` to throw a `RuntimeException` on write failures:
```php
use Vista\Logger\Failure\StrictFailureStrategy;

$handler = new StreamHandler(
    path: __DIR__ . '/app.log',
    minLevel: LogLevel::INFO,
    failureStrategy: new StrictFailureStrategy()
);
```
- Default: `ErrorLogFailureStrategy`
- Alternative: `StrictFailureStrategy`
- Custom implementations of `FailureStrategy` can be injected

Failure handling is explicit and local to each handler.

### `NullHandler`

Discards all log records (Null Object pattern).

Useful for testing or conditional logging.

---

## Formatters

### `LineFormatter` (default)

Human-readable single-line output:
```
[2026-02-17 14:32:10] info: User John logged in {"id":123}
```

Characteristics:
- Newline-terminated
- JSON context
- `JSON_THROW_ON_ERROR`
- No trailing whitespace

---

### `JsonFormatter`

Machine-friendly structured logging:
```json
{"timestamp":"2026-02-17T14:32:10+00:00","level":"info","message":"User John logged in","context":{"id":123}}
```

Characteristics:
- ISO 8601 timestamps
- Single-line JSON
- Newline-terminated
- `JSON_THROW_ON_ERROR`

---

## Message Interpolation

PSR-3 style placeholder replacement:
```php
$logger->info('Hello {name}', ['name' => 'John']);
```
- Only scalar and `Stringable` values are interpolated
- Non-interpolatable context values remain available to formatters
- Interpolation does not mutate context
- Missing placeholders are left untouched

---

## Failure Semantics

- Invalid log levels throw `InvalidArgumentException` immediately
- JSON encoding failures throw `JsonException`
- `StreamHandler` reports write failures via `error_log()` by default
- Failure handling is configurable via `FailureStrategy`
  - Strict mode escalates write failures via `RuntimeException`
  - Multi-handler execution is fail-fast
  - No silent swallowing of programmer errors

---

## Design Decisions

This package intentionally avoids:
- Async logging
- Channel systems
- Container integration
- Configuration loaders
- Event dispatchers
- Log rotation
- Buffering layers
- Implicit processors
- Speculative abstractions

The goal is a clean, extensible foundation that can be composed into larger systems without architectural debt.

---

## Testing Philosophy

- PHPUnit 12
- Deterministic timestamps
- Behavior-focused tests
- No brittle raw string comparisons
- No testing of private implementation details
- Strict resource cleanup
- PHPStan level 10 clean

---

## Versioning

This package adheres to Semantic Versioning (SemVer).

---

## Why not Monolog?

`monolog/monolog` is a powerful, feature-rich logging library and the ecosystem standard. If you need dozens of handlers, complex pipelines, buffering strategies, or broad integrations, Monolog is an excellent choice.

`vista-php/logger` is intentionally different.

It provides a strict, minimal PSR-3 implementation focused on architectural clarity, immutable log records, explicit failure semantics, and a small, predictable surface area. There are no channels, no buffering layers, no hidden processors, and no speculative abstractions.

Choose `Monolog` for ecosystem breadth.
Choose `vista-php/logger` for a clean, framework-grade logging foundation with explicit behavior and minimal complexity.

---

## License

MIT

---

## Contributing

Contributions are welcome, but this package is intentionally minimal and opinionated.

Before opening a pull request, please ensure:
- The change aligns with the core principles (SRP, minimal surface area, explicit behavior).
- No speculative abstractions are introduced.
- The feature solves a real, demonstrated use case.
- Tests are deterministic and behavior-focused.
- PHPStan level 10 passes.
- Coding style passes php-cs-fixer.
- Commit messages follow Conventional Commits.

### Non-Goals

The following will not be accepted:
- Async logging
- Channel systems
- Configuration loaders
- Container integrations
- Event dispatchers
- Buffering layers
- Implicit processors
- Log rotation
- Speculative extension points

If you need these features, consider composing this package or using `monolog/monolog`.

### Development Setup

```bash
composer install
composer analyze
composer test
composer check-style
```