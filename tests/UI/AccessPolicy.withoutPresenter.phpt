<?php declare(strict_types=1);

/**
 * Test: component factories work on a component that is not attached to a presenter
 */

use Nette\Application\Attributes\Requires;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class InnerControl extends Nette\Application\UI\Control
{
}

class StandaloneControl extends Nette\Application\UI\Control
{
	protected function createComponentPlain(): InnerControl
	{
		return new InnerControl;
	}


	#[Requires(forward: true)]
	protected function createComponentGuarded(): InnerControl
	{
		return new InnerControl;
	}
}


test('factory without #[Requires] needs no presenter', function () {
	$control = new StandaloneControl;
	Assert::type(InnerControl::class, $control['plain']);
});


test('factory with #[Requires] requires presenter to check it', function () {
	$control = new StandaloneControl;
	Assert::exception(
		fn() => $control['guarded'],
		Nette\InvalidStateException::class,
		'Presenter is required for checking requirements of StandaloneControl::createComponentGuarded()',
	);
});
