<?php

// Test: expect($x)->not->toBe() - triggers hasNotModifier and PropertyFetch branches
expect($value)->not->toBe(5);
expect($value)->not->toEqual(10);

// Deep chain with ->not
expect($arr)->not->toContain('item');
