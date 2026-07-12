<?php declare(strict_types=1);

/**
 * Test: cyclic switch() calls are detected instead of looping forever
 */

use Nette\Application;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/functions.php';


class SwitchLoopPresenter extends Nette\Application\UI\Presenter
{
	public function actionFoo(): void
	{
		$this->switch('bar');
	}


	public function actionBar(): void
	{
		$this->switch('foo');
	}
}


$presenter = createPresenter(SwitchLoopPresenter::class);
Assert::exception(
	fn() => $presenter->run(new Application\Request('', Application\Request::FORWARD, ['action' => 'foo'])),
	LogicException::class,
	'Infinite loop of switch() calls detected.',
);
