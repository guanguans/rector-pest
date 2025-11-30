<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\ChainExpectCallsRector;
use MrPunyapal\RectorPest\Rules\SimplifyComparisonExpectationsRector;
use MrPunyapal\RectorPest\Rules\SimplifyExpectNotRector;
use MrPunyapal\RectorPest\Rules\SimplifyToLiteralBooleanRector;
use MrPunyapal\RectorPest\Rules\ToBeTrueNotFalseRector;
use MrPunyapal\RectorPest\Rules\UseEachModifierRector;
use MrPunyapal\RectorPest\Rules\UseInstanceOfMatcherRector;
use MrPunyapal\RectorPest\Rules\UseToHaveCountRector;
use MrPunyapal\RectorPest\Rules\UseToMatchRector;
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

    $rectorConfig->rule(ChainExpectCallsRector::class);

    $rectorConfig->rule(SimplifyExpectNotRector::class);

    $rectorConfig->rule(ToBeTrueNotFalseRector::class);

    $rectorConfig->rule(UseEachModifierRector::class);

    $rectorConfig->rule(SimplifyToLiteralBooleanRector::class);

    $rectorConfig->rule(UseTypeMatchersRector::class);

    $rectorConfig->rule(UseToHaveCountRector::class);

    $rectorConfig->rule(UseInstanceOfMatcherRector::class);

    $rectorConfig->rule(SimplifyComparisonExpectationsRector::class);

    $rectorConfig->rule(UseToMatchRector::class);
};
