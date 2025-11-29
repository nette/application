<?php

/**
 * Test: Nette\Application\UI\Presenter::link() and #[Requires(forward: true)]
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Application\Attributes\Requires;
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


	#[Requires(forward: true)]
	public function actionFoo()
	{
	}


	#[Requires(forward: true)]
	public function handleFoo()
	{
	}


	#[Requires(forward: true)]
	public function renderBar()
	{
	}
}


#[Requires(forward: true)]
class ForwardPresenter extends TestPresenter
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
	E_USER_WARNING,
	"Invalid link: Link to forbidden action 'Test:foo' from 'Test:default'.",
);

Assert::error(
	fn() => $presenter->link('bar'),
	E_USER_WARNING,
	"Invalid link: Link to forbidden action 'Test:bar' from 'Test:default'.",
);

Assert::error(
	fn() => $presenter->link('foo!'),
	E_USER_WARNING,
	"Invalid link: Link to forbidden signal 'foo' from 'Test:default'.",
);

Assert::error(
	fn() => $presenter->link('Forward:'),
	E_USER_WARNING,
	"Invalid link: Link to forbidden presenter 'Forward' from 'Test:default'.",
);

Assert::noError(fn() => $presenter->link('Test:'));

Assert::error( // no error
	fn() => $presenter->forward('Forward:'),
	Application\AbortException::class,
);
