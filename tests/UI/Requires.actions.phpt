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


#[Requires(actions: ['second'])]
class TestActionsPresenter extends Nette\Application\UI\Presenter
{
	public function actionSecond(): never
	{
		$this->terminate();
	}
}


$presenter = createPresenter(TestActionsPresenter::class);

Assert::noError(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get, ['action' => 'second'])),
);

Assert::exception(
	fn() => $presenter->run(new Application\Request('', Http\Request::Get)),
	Application\BadRequestException::class,
	"Action 'default' is not allowed by TestActionsPresenter",
);
