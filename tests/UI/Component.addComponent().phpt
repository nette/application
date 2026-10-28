<?php declare(strict_types=1);

/**
 * Test: Nette\Application\UI\Component::addComponent() accepts only UI components
 */

use Nette\Application\UI;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class TestControl extends UI\Control
{
}


class ForeignComponent extends Nette\ComponentModel\Component
{
}


class ForeignStatePersistent extends Nette\ComponentModel\Component implements UI\StatePersistent
{
	public function loadState(array $params): void
	{
	}


	public function saveState(array &$params): void
	{
	}
}


$control = new TestControl;

Assert::noError(fn() => $control->addComponent(new TestControl, 'ok'));
$control->addComponent(new TestControl, 'first', insertBefore: 'ok');
Assert::same(['first', 'ok'], array_keys($control->getComponents()));

Assert::exception(
	fn() => $control->addComponent(new ForeignComponent, 'foreign'),
	Nette\InvalidStateException::class,
	"Component 'foreign' of type ForeignComponent is not intended to be used in the Presenter.",
);

Assert::exception(
	fn() => $control->addComponent(new ForeignStatePersistent, 'state'),
	Nette\InvalidStateException::class,
	"Component 'state' of type ForeignStatePersistent cannot carry persistent state, only Nette\\Application\\UI\\Component can.",
);
