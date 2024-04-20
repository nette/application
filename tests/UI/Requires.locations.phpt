<?php

/**
 * Test: Location of #[Requires]
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Application\Attributes\Requires;
use Nette\Http;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/functions.php';


#[Requires(forward: true)]
class TestClassPresenter extends Nette\Application\UI\Presenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}
}

class TestMethodActionPresenter extends Nette\Application\UI\Presenter
{
	#[Requires(forward: true)]
	public function actionDefault(): never
	{
		$this->terminate();
	}
}

class TestMethodRenderPresenter extends Nette\Application\UI\Presenter
{
	#[Requires(forward: true)]
	public function renderDefault(): never
	{
		$this->terminate();
	}
}

class TestMethodHandlePresenter extends Nette\Application\UI\Presenter
{
	#[Requires(forward: true)]
	public function handleFoo(): never
	{
		$this->terminate();
	}
}


class TestMethodCreateComponentPresenter extends Nette\Application\UI\Presenter
{
	#[Requires(forward: true)]
	public function createComponentFoo(): never
	{
		$this->terminate();
	}


	public function createComponentBar(): TestMethodCreateComponentControl
	{
		return new TestMethodCreateComponentControl;
	}
}

class TestMethodCreateComponentControl extends Nette\Application\UI\Control
{
	#[Requires(forward: true)]
	public function createComponentFoo(): never
	{
		$this->getPresenter()->terminate();
	}


	#[Requires(actions: 'foo')]
	public function createComponentBad(): void
	{
	}
}



// class-level attribute
$presenter = createPresenter(TestClassPresenter::class);
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Application\Request::FORWARD)),
);

Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
	Application\BadRequestException::class,
	'Forwarded request is required by TestClassPresenter',
);


// method action<name>()
$presenter = createPresenter(TestMethodActionPresenter::class);
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Application\Request::FORWARD)),
);

Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
	Application\BadRequestException::class,
	'Forwarded request is required by TestMethodActionPresenter::actionDefault()',
);


// method render<name>()
$presenter = createPresenter(TestMethodRenderPresenter::class);
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Application\Request::FORWARD)),
);

Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
	Application\BadRequestException::class,
	'Forwarded request is required by TestMethodRenderPresenter::renderDefault()',
);


// method handle<name>()
$presenter = createPresenter(TestMethodHandlePresenter::class);
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Application\Request::FORWARD, ['do' => 'foo'])),
);

Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get, ['do' => 'foo'])),
	Application\BadRequestException::class,
	'Forwarded request is required by TestMethodHandlePresenter::handleFoo()',
);


// method createComponent<name>()
$presenter = createPresenter(TestMethodCreateComponentPresenter::class);
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Application\Request::FORWARD, ['foo-a' => 1])),
);

Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get, ['foo-a' => 1])),
	Application\BadRequestException::class,
	'Forwarded request is required by TestMethodCreateComponentPresenter::createComponentFoo()',
);


// method createComponent<name>() in component
$presenter = createPresenter(TestMethodCreateComponentPresenter::class);
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Application\Request::FORWARD, ['bar-foo-a' => 1])),
);

Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get, ['bar-foo-a' => 1])),
	Application\BadRequestException::class,
	'Forwarded request is required by TestMethodCreateComponentControl::createComponentFoo()',
);


// option actions in method createComponent<name>() in component
$presenter = createPresenter(TestMethodCreateComponentPresenter::class);
Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get, ['bar-bad-a' => 1])),
	LogicException::class,
	'Requires(actions) used by TestMethodCreateComponentControl::createComponentBad() is allowed only in presenter.',
);
