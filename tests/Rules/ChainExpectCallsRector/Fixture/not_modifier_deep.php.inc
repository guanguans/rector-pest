<?php

// Test: ->not modifier in chain - triggers hasNotModifier
expect($value)->not->toBe(5);
expect($value)->not->toBeInt()->not->toBeNull();
