<?php

/**
 * Test: #[Requires] option sameSite
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Application\Attributes\Requires;
use Nette\Http;
use Nette\Http\Helpers;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/functions.php';


#[Requires(sameOrigin: true)]
class TestSameOriginPresenter extends Nette\Application\UI\Presenter
{
	public function actionDefault(): never
	{
		$this->terminate();
	}
}


$presenter = createPresenter(TestSameOriginPresenter::class, cookies: [Helpers::StrictCookieName => 1]);
Assert::type(
	Application\Responses\VoidResponse::class,
	$presenter->run(new Application\Request('', Http\Request::Get)),
);


$presenter = createPresenter(TestSameOriginPresenter::class);
Assert::type(
	Application\Responses\RedirectResponse::class,
	$presenter->run(new Application\Request('', Http\Request::Get)),
);
