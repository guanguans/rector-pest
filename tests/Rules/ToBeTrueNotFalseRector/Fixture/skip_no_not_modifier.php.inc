<?php

test('skips regular toBeTrue', function () {
    $value = true;

    expect($value)->toBeTrue();
});

?>
