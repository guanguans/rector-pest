<?php

expect($a)->toBe(10);
expect($b)->toContain('test');

-----
<?php

expect($a)->toBe(10)->and($b)->toContain('test');
