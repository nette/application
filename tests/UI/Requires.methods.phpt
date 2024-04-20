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
