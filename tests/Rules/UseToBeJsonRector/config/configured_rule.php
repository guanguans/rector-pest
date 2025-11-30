<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\UseToBeJsonRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToBeJsonRector::class]);
