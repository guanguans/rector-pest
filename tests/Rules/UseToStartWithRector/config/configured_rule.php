<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\UseToStartWithRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToStartWithRector::class]);
