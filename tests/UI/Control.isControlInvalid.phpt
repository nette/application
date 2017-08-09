<?php

/**
 * Test: Nette\Application\UI\Control::isControlInvalid()
 */

declare(strict_types=1);

use Nette\Application\UI;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestControl extends UI\Control
{
}


test(function () {
	$control = new TestControl;
	$child = new TestControl;
	$control->addComponent($child, 'foo');

	Assert::false($control->isControlInvalid());
	$child->redrawControl();
	Assert::true($control->isControlInvalid());
});


test(function () {
	$control = new TestControl;
	$child = new Nette\ComponentModel\Container;
	$grandChild = new TestControl;
	$control->addComponent($child, 'foo');
	$child->addComponent($grandChild, 'bar');

	Assert::false($control->isControlInvalid());
	$grandChild->redrawControl();
	Assert::true($control->isControlInvalid());
});
