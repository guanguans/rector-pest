<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\UseToEndWithRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToEndWithRector::class]);
