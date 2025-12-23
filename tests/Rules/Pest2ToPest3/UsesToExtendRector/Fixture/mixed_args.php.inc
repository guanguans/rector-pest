<?php

use Tests\Rules\Pest2ToPest3\UsesToExtendRector\Fixture\AnotherSampleTrait;
use Tests\Rules\Pest2ToPest3\UsesToExtendRector\Fixture\SampleTestCase;
use Tests\Rules\Pest2ToPest3\UsesToExtendRector\Fixture\SampleTrait;

// Mixed class and traits should be split
pest()->uses(SampleTestCase::class, SampleTrait::class, AnotherSampleTrait::class)->in('Feature');

?>
-----
<?php

use Tests\Rules\Pest2ToPest3\UsesToExtendRector\Fixture\AnotherSampleTrait;
use Tests\Rules\Pest2ToPest3\UsesToExtendRector\Fixture\SampleTestCase;
use Tests\Rules\Pest2ToPest3\UsesToExtendRector\Fixture\SampleTrait;

// Mixed class and traits should be split
pest()->extend(SampleTestCase::class)->use(SampleTrait::class, AnotherSampleTrait::class)->in('Feature');

?>
