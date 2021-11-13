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


$presenter = createPresenter(TestForwardPresenter::class);
Assert::noError(
	fn() => $presenter->run(new Application\Request('', Application\Request::FORWARD)),
);

Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
	Application\BadRequestException::class,
	'Forwarded request is required by TestForwardPresenter',
);
