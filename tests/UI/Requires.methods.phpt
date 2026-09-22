<?php declare(strict_types=1);

/**
 * Test: #[Requires] option actions
 */

use Nette\{Application, Http};
use Nette\Application\Attributes\Requires;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/functions.php';


#[Requires(methods: ['OPTIONS'])]
class TestMethodsPresenter extends Nette\Application\UI\Presenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


#[Requires(methods: ['*'])]
class TestAllMethodsPresenter extends Nette\Application\UI\Presenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


class TestActionMethodsPresenter extends Nette\Application\UI\Presenter
{
	#[Requires(methods: ['OPTIONS'])]
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


class TestActionAllMethodsPresenter extends Nette\Application\UI\Presenter
{
	#[Requires(methods: ['*'])]
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


class TestActionLowercaseMethodsPresenter extends Nette\Application\UI\Presenter
{
	#[Requires(methods: 'options')]
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


class TestRenderMethodsPresenter extends Nette\Application\UI\Presenter
{
	public function actionDefault(): void
	{
	}


	#[Requires(methods: ['OPTIONS'])]
	public function renderDefault(): never
	{
		$this->terminate();
	}
}


class TestUncalledRenderMethodsPresenter extends Nette\Application\UI\Presenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}


	#[Requires(methods: ['GET'])]
	public function renderDefault(): void
	{
	}
}


$presenter = createPresenter(TestMethodsPresenter::class, method: 'OPTIONS');
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
);


$presenter = createPresenter(TestMethodsPresenter::class);
Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
	Application\BadRequestException::class,
	'Method GET is not allowed by TestMethodsPresenter',
);


$presenter = createPresenter(TestAllMethodsPresenter::class, method: 'OPTIONS');
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
);


// attribute on the action method can allow methods outside the default whitelist
$presenter = createPresenter(TestActionMethodsPresenter::class, method: 'OPTIONS');
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
);


$presenter = createPresenter(TestActionMethodsPresenter::class);
Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
	Application\BadRequestException::class,
	'Method GET is not allowed by TestActionMethodsPresenter::actionDefault()',
);


$presenter = createPresenter(TestActionAllMethodsPresenter::class, method: 'TRACE');
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
);


$presenter = createPresenter(TestActionLowercaseMethodsPresenter::class, method: 'OPTIONS');
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
);


// method outside both the whitelist and the attribute is rejected by the whitelist
$presenter = createPresenter(TestActionMethodsPresenter::class, method: 'TRACE');
Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
	Application\BadRequestException::class,
	'Method TRACE is not allowed',
);


// attribute on the render method counts as well
$presenter = createPresenter(TestRenderMethodsPresenter::class, method: 'OPTIONS');
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
);


// render method may never be called, so its attribute cannot replace the whitelist
$presenter = createPresenter(TestUncalledRenderMethodsPresenter::class, method: 'TRACE');
Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
	Application\BadRequestException::class,
	'Method TRACE is not allowed',
);
