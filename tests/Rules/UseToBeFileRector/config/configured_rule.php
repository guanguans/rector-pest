<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\UseToBeFileRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToBeFileRector::class]);
