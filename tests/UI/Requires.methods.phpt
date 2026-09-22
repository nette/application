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


// attribute on the render method counts as well
$presenter = createPresenter(TestRenderMethodsPresenter::class, method: 'OPTIONS');
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
);
