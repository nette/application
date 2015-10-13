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

Assert::same(array(10, 'str'), PresenterComponentReflection::combineArgs($reflection, array('intParam' => '10', 'strParam' => 'str')));
Assert::same(array(0, ''), PresenterComponentReflection::combineArgs($reflection, array()));
