<?php

/**
 * Test: Nette\Application\UI\Multiplier
 */

declare(strict_types=1);

use Nette\Application;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestControl extends Application\UI\Control
{
	public function __construct(
		public string $id,
	) {
	}


	public function handleClick(): void
	{
	}
}


test('factory creates components dynamically', function () {
	$calls = [];
	$multiplier = new Application\UI\Multiplier(function ($name) use (&$calls) {
		$calls[] = $name;
		return new TestControl($name);
	});

	$component1 = $multiplier->getComponent('item1');
	$component2 = $multiplier->getComponent('item2');

	Assert::type(TestControl::class, $component1);
	Assert::type(TestControl::class, $component2);
	Assert::same('item1', $component1->id);
	Assert::same('item2', $component2->id);
	Assert::same(['item1', 'item2'], $calls);
});


test('same name returns cached component', function () {
	$calls = 0;
	$multiplier = new Application\UI\Multiplier(function ($name) use (&$calls) {
		$calls++;
		return new TestControl($name);
	});

	$component1 = $multiplier->getComponent('item1');
	$component2 = $multiplier->getComponent('item1');

	Assert::same($component1, $component2);
	Assert::same(1, $calls);
});


test('factory receives component name and parent', function () {
	$receivedName = null;
	$receivedParent = null;

	$multiplier = new Application\UI\Multiplier(function ($name, $parent) use (&$receivedName, &$receivedParent) {
		$receivedName = $name;
		$receivedParent = $parent;
		return new TestControl($name);
	});

	$multiplier->getComponent('test');

	Assert::same('test', $receivedName);
	Assert::same($multiplier, $receivedParent);
});
