<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\ChainExpectCallsRector;
use MrPunyapal\RectorPest\Rules\SimplifyComparisonExpectationsRector;
use MrPunyapal\RectorPest\Rules\SimplifyExpectNotRector;
use MrPunyapal\RectorPest\Rules\SimplifyToLiteralBooleanRector;
use MrPunyapal\RectorPest\Rules\ToBeTrueNotFalseRector;
use MrPunyapal\RectorPest\Rules\UseEachModifierRector;
use MrPunyapal\RectorPest\Rules\UseInstanceOfMatcherRector;
use MrPunyapal\RectorPest\Rules\UseStrictEqualityMatchersRector;
use MrPunyapal\RectorPest\Rules\UseToBeDirectoryRector;
use MrPunyapal\RectorPest\Rules\UseToBeFileRector;
use MrPunyapal\RectorPest\Rules\UseToBeJsonRector;
use MrPunyapal\RectorPest\Rules\UseToBeReadableWritableRector;
use MrPunyapal\RectorPest\Rules\UseToContainRector;
use MrPunyapal\RectorPest\Rules\UseToEndWithRector;
use MrPunyapal\RectorPest\Rules\UseToHaveCountRector;
use MrPunyapal\RectorPest\Rules\UseToHaveKeyRector;
use MrPunyapal\RectorPest\Rules\UseToHaveLengthRector;
use MrPunyapal\RectorPest\Rules\UseToHavePropertyRector;
use MrPunyapal\RectorPest\Rules\UseToMatchRector;
use MrPunyapal\RectorPest\Rules\UseToStartWithRector;
use MrPunyapal\RectorPest\Rules\UseTypeMatchersRector;
use Rector\Config\RectorConfig;

/**
 * Code quality improvements for Pest tests
 *
 * This set contains rules for:
 * - Better test readability and expressiveness
 * - Removing redundant code in tests
 * - Using more expressive Pest APIs
 * - Simplifying expect chains
 * - Using dedicated matchers instead of generic comparisons
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    // Chaining and structure
    $rectorConfig->rule(ChainExpectCallsRector::class);
    $rectorConfig->rule(UseEachModifierRector::class);

    // Boolean and negation simplification
    $rectorConfig->rule(SimplifyExpectNotRector::class);
    $rectorConfig->rule(ToBeTrueNotFalseRector::class);
    $rectorConfig->rule(SimplifyToLiteralBooleanRector::class);

    // Type matchers
    $rectorConfig->rule(UseTypeMatchersRector::class);
    $rectorConfig->rule(UseInstanceOfMatcherRector::class);

    // Comparison matchers
    $rectorConfig->rule(SimplifyComparisonExpectationsRector::class);
    $rectorConfig->rule(UseStrictEqualityMatchersRector::class);

    // Array matchers
    $rectorConfig->rule(UseToContainRector::class);
    $rectorConfig->rule(UseToHaveKeyRector::class);
    $rectorConfig->rule(UseToHaveCountRector::class);

    // String matchers
    $rectorConfig->rule(UseToStartWithRector::class);
    $rectorConfig->rule(UseToEndWithRector::class);
    $rectorConfig->rule(UseToHaveLengthRector::class);
    $rectorConfig->rule(UseToMatchRector::class);
    $rectorConfig->rule(UseToBeJsonRector::class);

    // File system matchers
    $rectorConfig->rule(UseToBeFileRector::class);
    $rectorConfig->rule(UseToBeDirectoryRector::class);
    $rectorConfig->rule(UseToBeReadableWritableRector::class);

    // Object matchers
    $rectorConfig->rule(UseToHavePropertyRector::class);
};
