<?php

// Test case: expect with ->not modifier
// This exercises the PropertyFetch path in AbstractRector::getExpectFuncCall (line 48-52)
// Same variable but with ->not - should NOT chain since ->not changes semantics
expect($value)->not->toBeFalse();
expect($value)->not->toBe(10);

?>
-----
<?php

// Test case: expect with ->not modifier
// This exercises the PropertyFetch path in AbstractRector::getExpectFuncCall (line 48-52)
// Same variable but with ->not - should NOT chain since ->not changes semantics
expect($value)->not->toBeFalse();
expect($value)->not->toBe(10);

?>
