<?php

// Skip: Same variables (should use ChainSameVariableExpectCallsRector)
expect($a)->toBe(10);
expect($a)->toBeInt();

// Skip: Single expect call
expect($single)->toBeTrue();
