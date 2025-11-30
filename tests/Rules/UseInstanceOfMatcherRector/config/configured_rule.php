<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\UseInstanceOfMatcherRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseInstanceOfMatcherRector::class);
};
