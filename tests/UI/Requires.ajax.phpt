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


#[Requires(ajax: true)]
class TestAjaxPresenter extends Nette\Application\UI\Presenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


$presenter = createPresenter(TestAjaxPresenter::class, headers: ['X-Requested-With' => 'XMLHttpRequest']);
Assert::noError(
	fn() => $presenter->run(new Application\Request('Test', Http\Request::Get)),
);


$presenter = createPresenter(TestAjaxPresenter::class);
Assert::exception(
	fn() => $presenter->run(new Application\Request('Test', Http\Request::Get)),
	Application\BadRequestException::class,
	'AJAX request is required by TestAjaxPresenter',
);
