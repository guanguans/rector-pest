<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\UseToHaveLengthRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToHaveLengthRector::class]);
