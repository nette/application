<?php

/**
 * Test: Nette\Application\UI\Presenter flash messages
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/functions.php';


class TestPresenter extends Application\UI\Presenter
{
	public function renderDefault()
	{
		$this->terminate();
	}
}


class FlashPresenter extends Application\UI\Presenter
{
	public function actionDefault()
	{
		$this->flashMessage('Test message');
	}


	public function renderDefault()
	{
		$this->terminate();
	}
}


test('flash message is stored in session', function () {
	$sessionSection = Mockery::mock(Http\SessionSection::class);
	$sessionSection->shouldReceive('get')->andReturn([]);
	$sessionSection->shouldReceive('set')->once();

	$session = Mockery::mock(Http\Session::class);
	$session->shouldReceive('getSection')->andReturn($sessionSection);
	$session->shouldReceive('hasSection')->andReturn(true);

	$latte = Mockery::mock(Latte\Engine::class);
	$latte->shouldIgnoreMissing();
	$templateFactory = Mockery::mock(Application\UI\TemplateFactory::class);
	$templateFactory->shouldReceive('createTemplate')->andReturn(new Nette\Bridges\ApplicationLatte\DefaultTemplate($latte));

	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		session: $session,
		templateFactory: $templateFactory,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Test', 'GET', ['action' => 'default']));

	$flash = $presenter->flashMessage('Test message');
	Assert::type(stdClass::class, $flash);
	Assert::same('Test message', $flash->message);
	Assert::same('info', $flash->type);
});


test('flash message with custom type', function () {
	$sessionSection = Mockery::mock(Http\SessionSection::class);
	$sessionSection->shouldReceive('get')->andReturn([]);
	$sessionSection->shouldReceive('set')->once();

	$session = Mockery::mock(Http\Session::class);
	$session->shouldReceive('getSection')->andReturn($sessionSection);
	$session->shouldReceive('hasSection')->andReturn(true);

	$latte = Mockery::mock(Latte\Engine::class);
	$latte->shouldIgnoreMissing();
	$templateFactory = Mockery::mock(Application\UI\TemplateFactory::class);
	$templateFactory->shouldReceive('createTemplate')->andReturn(new Nette\Bridges\ApplicationLatte\DefaultTemplate($latte));

	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		session: $session,
		templateFactory: $templateFactory,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Test', 'GET', ['action' => 'default']));

	$flash = $presenter->flashMessage('Error occurred', 'error');
	Assert::same('Error occurred', $flash->message);
	Assert::same('error', $flash->type);
});


test('multiple flash messages are stored', function () {
	$messages = [];
	$sessionSection = Mockery::mock(Http\SessionSection::class);
	$sessionSection->shouldReceive('get')->andReturnUsing(function () use (&$messages) {
		return $messages;
	});
	$sessionSection->shouldReceive('set')->andReturnUsing(function ($id, $value) use (&$messages) {
		$messages = $value;
	});

	$session = Mockery::mock(Http\Session::class);
	$session->shouldReceive('getSection')->andReturn($sessionSection);
	$session->shouldReceive('hasSection')->andReturn(true);

	$latte = Mockery::mock(Latte\Engine::class);
	$latte->shouldIgnoreMissing();
	$templateFactory = Mockery::mock(Application\UI\TemplateFactory::class);
	$templateFactory->shouldReceive('createTemplate')->andReturn(new Nette\Bridges\ApplicationLatte\DefaultTemplate($latte));

	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		session: $session,
		templateFactory: $templateFactory,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Test', 'GET', ['action' => 'default']));

	$presenter->flashMessage('First message', 'info');
	$presenter->flashMessage('Second message', 'error');
	$presenter->flashMessage('Third message', 'success');

	Assert::count(3, $messages);
	Assert::same('First message', $messages[0]->message);
	Assert::same('info', $messages[0]->type);
	Assert::same('Second message', $messages[1]->message);
	Assert::same('error', $messages[1]->type);
	Assert::same('Third message', $messages[2]->message);
	Assert::same('success', $messages[2]->type);
});


test('flash message is available in template', function () {
	$messages = [];
	$sessionSection = Mockery::mock(Http\SessionSection::class);
	$sessionSection->shouldReceive('get')->andReturnUsing(function () use (&$messages) {
		return $messages;
	});
	$sessionSection->shouldReceive('set')->andReturnUsing(function ($id, $value) use (&$messages) {
		$messages = $value;
	});

	$session = Mockery::mock(Http\Session::class);
	$session->shouldReceive('getSection')->andReturn($sessionSection);
	$session->shouldReceive('hasSection')->andReturn(true);

	$latte = Mockery::mock(Latte\Engine::class);
	$latte->shouldIgnoreMissing();
	$templateFactory = Mockery::mock(Application\UI\TemplateFactory::class);
	$templateFactory->shouldReceive('createTemplate')->andReturn(new Nette\Bridges\ApplicationLatte\DefaultTemplate($latte));

	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		session: $session,
		templateFactory: $templateFactory,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Test', 'GET', ['action' => 'default']));

	$presenter->flashMessage('Template message');

	$template = $presenter->getTemplate();
	Assert::count(1, $template->flashes);
	Assert::same('Template message', $template->flashes[0]->message);
});


test('flash session expires after presenter run', function () {
	$expirationSet = false;
	$sessionSection = Mockery::mock(Http\SessionSection::class);
	$sessionSection->shouldReceive('get')->andReturn([]);
	$sessionSection->shouldReceive('set')->once();
	$sessionSection->shouldReceive('setExpiration')->once()->with('30 seconds')->andReturnUsing(function () use (&$expirationSet, $sessionSection) {
		$expirationSet = true;
		return $sessionSection;
	});

	$session = Mockery::mock(Http\Session::class);
	$session->shouldReceive('getSection')->andReturn($sessionSection);
	$session->shouldReceive('hasSection')->andReturn(true);

	$latte = Mockery::mock(Latte\Engine::class);
	$latte->shouldIgnoreMissing();
	$templateFactory = Mockery::mock(Application\UI\TemplateFactory::class);
	$templateFactory->shouldReceive('createTemplate')->andReturn(new Nette\Bridges\ApplicationLatte\DefaultTemplate($latte));

	$presenter = new FlashPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		session: $session,
		templateFactory: $templateFactory,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Flash', 'GET', ['action' => 'default']));

	Assert::true($expirationSet);
});


test('flash message accepts stdClass object', function () {
	$sessionSection = Mockery::mock(Http\SessionSection::class);
	$sessionSection->shouldReceive('get')->andReturn([]);
	$sessionSection->shouldReceive('set')->once();

	$session = Mockery::mock(Http\Session::class);
	$session->shouldReceive('getSection')->andReturn($sessionSection);
	$session->shouldReceive('hasSection')->andReturn(true);

	$latte = Mockery::mock(Latte\Engine::class);
	$latte->shouldIgnoreMissing();
	$templateFactory = Mockery::mock(Application\UI\TemplateFactory::class);
	$templateFactory->shouldReceive('createTemplate')->andReturn(new Nette\Bridges\ApplicationLatte\DefaultTemplate($latte));

	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript),
		new Http\Response,
		session: $session,
		templateFactory: $templateFactory,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Test', 'GET', ['action' => 'default']));

	$customFlash = (object) [
		'message' => 'Custom message',
		'type' => 'warning',
		'custom' => 'data',
	];
	$flash = $presenter->flashMessage($customFlash);

	Assert::same($customFlash, $flash);
	Assert::same('Custom message', $flash->message);
	Assert::same('warning', $flash->type);
	Assert::same('data', $flash->custom);
});
