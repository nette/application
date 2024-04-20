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


class TestMethodHandlePresenter extends Nette\Application\UI\Presenter
{
	public function handleFoo()
	{
		$this->terminate();
	}
}


class TestMethodHandleSameOriginPresenter extends Nette\Application\UI\Presenter
{
	#[Requires(sameOrigin: true)]
	public function handleFoo()
	{
		$this->terminate();
	}
}


class TestMethodHandleSameOriginDisabledPresenter extends Nette\Application\UI\Presenter
{
	#[Requires(sameOrigin: false)]
	public function handleFoo()
	{
		$this->terminate();
	}
}


class TestMethodHandleCrossOriginPresenter extends Nette\Application\UI\Presenter
{
	#[Application\Attributes\CrossOrigin]
	public function handleFoo()
	{
		$this->terminate();
	}
}


// method handle<name>() requires same origin
$presenter = createPresenter(TestMethodHandlePresenter::class, cookies: [Helpers::StrictCookieName => 1]);
Assert::type(
	Application\Responses\VoidResponse::class,
	$presenter->run(new Application\Request('', Http\Request::Get, ['do' => 'foo'])),
);

$presenter = createPresenter(TestMethodHandlePresenter::class);
Assert::type(
	Application\Responses\RedirectResponse::class,
	$presenter->run(new Application\Request('', Http\Request::Get, ['do' => 'foo'])),
);


// #[Requires(sameOrigin: true)] still requires same origin
$presenter = createPresenter(TestMethodHandleSameOriginPresenter::class, cookies: [Helpers::StrictCookieName => 1]);
Assert::type(
	Application\Responses\VoidResponse::class,
	$presenter->run(new Application\Request('', Http\Request::Get, ['do' => 'foo'])),
);

$presenter = createPresenter(TestMethodHandleSameOriginPresenter::class);
Assert::type(
	Application\Responses\RedirectResponse::class,
	$presenter->run(new Application\Request('', Http\Request::Get, ['do' => 'foo'])),
);


// #[Requires(sameOrigin: false)] cancels the requirement
$presenter = createPresenter(TestMethodHandleSameOriginDisabledPresenter::class, cookies: [Helpers::StrictCookieName => 1]);
Assert::type(
	Application\Responses\VoidResponse::class,
	$presenter->run(new Application\Request('', Http\Request::Get, ['do' => 'foo'])),
);

$presenter = createPresenter(TestMethodHandleSameOriginDisabledPresenter::class);
Assert::type(
	Application\Responses\VoidResponse::class,
	$presenter->run(new Application\Request('', Http\Request::Get, ['do' => 'foo'])),
);


// #[CrossOrigin] cancels the requirement
$presenter = createPresenter(TestMethodHandleCrossOriginPresenter::class, cookies: [Helpers::StrictCookieName => 1]);
Assert::type(
	Application\Responses\VoidResponse::class,
	$presenter->run(new Application\Request('', Http\Request::Get, ['do' => 'foo'])),
);

$presenter = createPresenter(TestMethodHandleCrossOriginPresenter::class);
Assert::type(
	Application\Responses\VoidResponse::class,
	$presenter->run(new Application\Request('', Http\Request::Get, ['do' => 'foo'])),
);
