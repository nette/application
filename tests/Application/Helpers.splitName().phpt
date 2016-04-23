<?php

/**
 * Test: Helpers::splitName()
 */

use Nette\Application\Helpers;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same(['', '', ''], Helpers::splitName(''));
Assert::same(['', 'One', ':'], Helpers::splitName(':One'));
Assert::same(['Module', '', ':'], Helpers::splitName('Module:'));
Assert::same(['Module', 'One', ':'], Helpers::splitName('Module:One'));
Assert::same(['Module:Submodule', 'One', ':'], Helpers::splitName('Module:Submodule:One'));
