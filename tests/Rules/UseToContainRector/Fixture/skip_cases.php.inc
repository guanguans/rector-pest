<?php

// Skip non-expect chains
$value = $item->method();

// Skip non-expect toBe target
expect($value)->toBeString();
