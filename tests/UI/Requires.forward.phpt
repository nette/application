<?php

/**
 * Test: #[Requires] option actions
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Application\Attributes\Requires;
use Nette\Http;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/functions.php';


#[Requires(forward: true)]
class TestForwardPresenter extends Nette\Application\UI\Presenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


class TestForwardViewPresenter extends Nette\Application\UI\Presenter
{
	public function actionDefault(): void
	{
		$this->setView('forward');
	}


	#[Requires(forward: true)]
	public function renderForward(): void
	{
		$this->terminate();
	}
}


class TestSwitchViewPresenter extends Nette\Application\UI\Presenter
{
	public function actionDefault(): void
	{
		$this->switch('switch');
	}


	#[Requires(forward: true)]
	public function actionSwitch(): void
	{
		$this->terminate();
	}
}


// forwarded request
$presenter = createPresenter(TestForwardPresenter::class);
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Application\Request::FORWARD)),
);

Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
	Application\BadRequestException::class,
	'Forwarded request is required by TestForwardPresenter',
);


// changed view
$presenter = createPresenter(TestForwardViewPresenter::class);
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
);

Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get, ['action' => 'forward'])),
	Application\BadRequestException::class,
	'Forwarded request is required by TestForwardViewPresenter::renderForward()',
);


// switched view
$presenter = createPresenter(TestSwitchViewPresenter::class);
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
);

Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get, ['action' => 'switch'])),
	Application\BadRequestException::class,
	'Forwarded request is required by TestSwitchViewPresenter::actionSwitch()',
);
