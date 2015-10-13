<?php

/**
 * Test: PresenterComponentReflection::combineArgs()
 */

use Nette\Application\UI\PresenterComponentReflection;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class MyPresenter
{

	public function myMethod($intParam = 0, $strParam = '')
	{
	}

}

$reflection = new ReflectionMethod('MyPresenter', 'myMethod');

Assert::same([10, 'str'], PresenterComponentReflection::combineArgs($reflection, ['intParam' => '10', 'strParam' => 'str']));
Assert::same([0, ''], PresenterComponentReflection::combineArgs($reflection, []));
