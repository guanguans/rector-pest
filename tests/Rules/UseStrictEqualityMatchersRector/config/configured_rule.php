<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\UseStrictEqualityMatchersRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseStrictEqualityMatchersRector::class]);
