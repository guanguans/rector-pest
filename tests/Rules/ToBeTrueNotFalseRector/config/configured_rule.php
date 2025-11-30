<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\ToBeTrueNotFalseRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ToBeTrueNotFalseRector::class);
};
