<?php

/**
 * Test: Nette\Application\UI\Presenter lifecycle
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/functions.php';


class LifecyclePresenter extends Application\UI\Presenter
{
	public array $called = [];


	protected function startup(): void
	{
		parent::startup();
		$this->called[] = 'startup';
	}


	public function actionDefault(): void
	{
		$this->called[] = 'actionDefault';
	}


	protected function beforeRender(): void
	{
		parent::beforeRender();
		$this->called[] = 'beforeRender';
	}


	public function renderDefault(): void
	{
		$this->called[] = 'renderDefault';
		$this->terminate();
	}


	protected function afterRender(): void
	{
		parent::afterRender();
		$this->called[] = 'afterRender';
	}


	protected function shutdown(Application\Response $response): void
	{
		parent::shutdown($response);
		$this->called[] = 'shutdown';
	}
}


test('lifecycle methods are called in correct order', function () {
	$latte = Mockery::mock(Latte\Engine::class);
	$latte->shouldIgnoreMissing();
	$templateFactory = Mockery::mock(Application\UI\TemplateFactory::class);
	$templateFactory->shouldReceive('createTemplate')->andReturn(new Nette\Bridges\ApplicationLatte\DefaultTemplate($latte));

	$presenter = new LifecyclePresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		templateFactory: $templateFactory,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Lifecycle', 'GET', ['action' => 'default']));

	Assert::same(
		['startup', 'actionDefault', 'beforeRender', 'renderDefault', 'shutdown'],
		$presenter->called,
	);
});


test('onStartup event is fired before startup', function () {
	$eventCalled = false;

	$latte = Mockery::mock(Latte\Engine::class);
	$latte->shouldIgnoreMissing();
	$templateFactory = Mockery::mock(Application\UI\TemplateFactory::class);
	$templateFactory->shouldReceive('createTemplate')->andReturn(new Nette\Bridges\ApplicationLatte\DefaultTemplate($latte));

	$presenter = new LifecyclePresenter;
	$presenter->onStartup[] = function () use (&$eventCalled, $presenter) {
		$eventCalled = true;
		Assert::same([], $presenter->called);
	};

	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		templateFactory: $templateFactory,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Lifecycle', 'GET', ['action' => 'default']));

	Assert::true($eventCalled);
});


test('onRender event is fired after beforeRender', function () {
	$eventCalled = false;

	$latte = Mockery::mock(Latte\Engine::class);
	$latte->shouldIgnoreMissing();
	$templateFactory = Mockery::mock(Application\UI\TemplateFactory::class);
	$templateFactory->shouldReceive('createTemplate')->andReturn(new Nette\Bridges\ApplicationLatte\DefaultTemplate($latte));

	$presenter = new LifecyclePresenter;
	$presenter->onRender[] = function () use (&$eventCalled, $presenter) {
		$eventCalled = true;
		Assert::same(['startup', 'actionDefault', 'beforeRender'], $presenter->called);
	};

	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		templateFactory: $templateFactory,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Lifecycle', 'GET', ['action' => 'default']));

	Assert::true($eventCalled);
});


test('onShutdown event is fired before shutdown', function () {
	$eventCalled = false;

	$latte = Mockery::mock(Latte\Engine::class);
	$latte->shouldIgnoreMissing();
	$templateFactory = Mockery::mock(Application\UI\TemplateFactory::class);
	$templateFactory->shouldReceive('createTemplate')->andReturn(new Nette\Bridges\ApplicationLatte\DefaultTemplate($latte));

	$presenter = new LifecyclePresenter;
	$presenter->onShutdown[] = function () use (&$eventCalled, $presenter) {
		$eventCalled = true;
		Assert::same(['startup', 'actionDefault', 'beforeRender', 'renderDefault'], $presenter->called);
	};

	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		templateFactory: $templateFactory,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Lifecycle', 'GET', ['action' => 'default']));

	Assert::true($eventCalled);
});


class SignalPresenter extends Application\UI\Presenter
{
	public array $called = [];


	protected function startup(): void
	{
		parent::startup();
		$this->called[] = 'startup';
	}


	public function handleRefresh(): void
	{
		$this->called[] = 'handleRefresh';
	}


	public function actionDefault(): void
	{
		$this->called[] = 'actionDefault';
	}


	public function renderDefault(): void
	{
		$this->called[] = 'renderDefault';
		$this->terminate();
	}
}


test('signal is called after action', function () {
	$presenter = createPresenter(SignalPresenter::class, cookies: [Http\Helpers::StrictCookieName => 1]);

	$presenter->run(new Application\Request('Signal', 'GET', [
		'action' => 'default',
		'do' => 'refresh',
	]));

	Assert::same(['startup', 'actionDefault', 'handleRefresh', 'renderDefault'], $presenter->called);
});


class RedirectPresenter extends Application\UI\Presenter
{
	public array $called = [];


	public function actionDefault(): void
	{
		$this->called[] = 'actionDefault';
		$this->redirect('other');
	}


	public function actionOther(): void
	{
		$this->called[] = 'actionOther';
	}


	public function renderOther(): void
	{
		$this->called[] = 'renderOther';
		$this->terminate();
	}
}


test('redirect terminates current lifecycle', function () {
	$presenter = createPresenter(RedirectPresenter::class);

	$response = $presenter->run(new Application\Request('Redirect', 'GET', ['action' => 'default']));

	Assert::type(Application\Responses\RedirectResponse::class, $response);
	Assert::same(['actionDefault'], $presenter->called);
	Assert::notContains('renderDefault', $presenter->called);
	Assert::notContains('renderOther', $presenter->called);
});
