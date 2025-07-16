<?php

/**
 * Test: Nette\Application\UI\Component::createComponent()
 */

use Nette\Application;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class TestSubComponent extends Application\UI\Component
{
}

class TestComponent extends Application\UI\Component
{
	public $calledWith;

	public function checkRequirements($element)
	{
		$this->calledWith = $element;
	}

	public function createComponentTest()
	{
		return new TestSubComponent;
	}
}

$component = new TestComponent;
Assert::true($component->getComponent('test') instanceof TestSubComponent);
Assert::true($component->calledWith instanceof ReflectionMethod);
Assert::same('createComponentTest', $component->calledWith->getName());
Assert::same(TestComponent::class, $component->calledWith->getDeclaringClass()->getName());
