# Rector Pest

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrpunyapal/rector-pest.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/rector-pest)

Rector rules for [PestPHP](https://pestphp.com/) to improve code quality and help with version upgrades.

## Installation

```bash
composer require --dev mrpunyapal/rector-pest
```

## Available Rule Sets

### Code Quality

Improve your Pest tests with better readability and expressiveness.

```php
// rector.php
use MrPunyapal\RectorPest\Set\PestSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/tests',
    ])
    ->withSets([
        PestSetList::PEST_CODE_QUALITY,
    ]);
```

**Included Rules:**

| Rule | Description |
|------|-------------|
| `ChainExpectCallsRector` | Chains multiple `expect()` calls on the same value using `->and()` |
| `SimplifyExpectNotRector` | Converts `expect(!$x)->toBeTrue()` to `expect($x)->not->toBeTrue()` |
| `ToBeTrueNotFalseRector` | Simplifies `->not->toBeFalse()` to `->toBeTrue()` and vice versa |
| `UseEachModifierRector` | Converts `foreach` loops with `expect()` to `->each` modifier |

## Automate Pest Upgrades

Use `PestLevelSetList` to automatically upgrade to a specific Pest version. Sets for higher versions include sets for lower versions.

```php
// rector.php
use MrPunyapal\RectorPest\Set\PestLevelSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/tests',
    ])
    ->withSets([
        PestLevelSetList::UP_TO_PEST_40,
    ]);
```

### Manual Version Configuration

Use `PestSetList` if you only want changes for a specific version:

```php
// rector.php
use MrPunyapal\RectorPest\Set\PestSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/tests',
    ])
    ->withSets([
        PestSetList::PEST_30, // Only v2→v3 changes
    ]);
```

### Pest v3 (PEST_30)

Rules for upgrading from Pest v2 to v3:

| Rule | Description |
|------|-------------|
| `TapToDeferRector` | Replaces deprecated `->tap()` with `->defer()` |
| `ToHaveMethodOnClassRector` | Changes `expect($object)->toHaveMethod()` to `expect($object::class)->toHaveMethod()` |

### Pest v4 (PEST_40)

Rules for upgrading from Pest v3 to v4:

> **Note:** Pest v4 primarily requires dependency updates (PHPUnit 12, PHP 8.3+) with minimal code changes. Rules will be added as migration patterns emerge.

## Individual Rules

You can also use individual rules:

```php
// rector.php
use MrPunyapal\RectorPest\Rules\ChainExpectCallsRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/tests',
    ])
    ->withRules([
        ChainExpectCallsRector::class,
    ]);
```

## Rule Examples

### ChainExpectCallsRector

```php
// Before
expect($value)->toBe(10);
expect($value)->toBeInt();

// After
expect($value)->toBe(10)
    ->and($value)->toBeInt();
```

### SimplifyExpectNotRector

```php
// Before
expect(!$condition)->toBeTrue();

// After
expect($condition)->not->toBeTrue();
```

### ToBeTrueNotFalseRector

```php
// Before
expect($value)->not->toBeFalse();

// After
expect($value)->toBeTrue();
```

### UseEachModifierRector

```php
// Before
foreach ($items as $item) {
    expect($item)->toBeString();
}

// After
expect($items)->each->toBeString();
```

### TapToDeferRector (v3)

```php
// Before
expect($value)->tap(fn ($v) => dump($v))->toBe(10);

// After
expect($value)->defer(fn ($v) => dump($v))->toBe(10);
```

### ToHaveMethodOnClassRector (v3)

```php
// Before
expect($user)->toHaveMethod('getName');

// After
expect($user::class)->toHaveMethod('getName');
```

## Running Rector

```bash
# Preview changes
vendor/bin/rector process --dry-run

# Apply changes
vendor/bin/rector process
```

## Requirements

- PHP 8.2+
- Rector 2.0+

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
