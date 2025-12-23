<?php

// Test: consecutive expect() with ->not modifier on same variable
expect($value)->not->toBe(5);
expect($value)->toBeInt();

test('sample', function () {
    // Consecutive expects with comments should not be merged
    expect($value)->toBe(1); // keep separate
    expect($value)->toBeInt();
});
