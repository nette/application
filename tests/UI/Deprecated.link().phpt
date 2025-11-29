<?php

/**
 * Test: Nette\Application\UI\Presenter::link() and #[Deprecated]
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Application\Attributes\Deprecated;
use Nette\Http;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	protected function startup(): void
	{
		parent::startup();
		$this->terminate();
	}


	#[Deprecated]
	public function actionFoo()
	{
	}


	#[Deprecated]
	public function handleFoo()
	{
	}


	#[Deprecated]
	public function renderBar()
	{
	}
}


#[Deprecated]
class DeprecatedPresenter extends TestPresenter
{
}


$url = new Http\UrlScript('http://localhost/index.php', '/index.php');

$presenterFactory = Mockery::mock(Nette\Application\IPresenterFactory::class);
$presenterFactory->shouldReceive('getPresenterClass')
	->andReturnUsing(fn($presenter) => $presenter . 'Presenter');

$presenter = new TestPresenter;
$presenter->injectPrimary(
	new Http\Request($url),
	new Http\Response,
	$presenterFactory,
	new Application\Routers\SimpleRouter,
);

$presenter->invalidLinkMode = TestPresenter::InvalidLinkWarning;

$request = new Application\Request('Test', Http\Request::Get, []);
$presenter->run($request);

Assert::error(
	fn() => $presenter->link('foo'),
	E_USER_DEPRECATED,
	"Link to deprecated action 'Test:foo' from 'Test:default'.",
);

Assert::error(
	fn() => $presenter->link('bar'),
	E_USER_DEPRECATED,
	"Link to deprecated action 'Test:bar' from 'Test:default'.",
);

Assert::error(
	fn() => $presenter->link('foo!'),
	E_USER_DEPRECATED,
	"Link to deprecated signal 'foo' from 'Test:default'.",
);

Assert::error(
	fn() => $presenter->link('Deprecated:'),
	E_USER_DEPRECATED,
	"Link to deprecated presenter 'Deprecated' from 'Test:default'.",
);

Assert::noError(
	fn() => $presenter->link('Test:'),
);
