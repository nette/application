<?php

/**
 * Test: Nette\Application\UI\Component signal handling
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class NestedControl extends Application\UI\Control
{
	public bool $signalCalled = false;


	public function handleRefresh(): void
	{
		$this->signalCalled = true;
	}
}


class ParentControl extends Application\UI\Control
{
	public bool $signalCalled = false;


	public function handleUpdate(): void
	{
		$this->signalCalled = true;
	}


	protected function createComponentNested(): NestedControl
	{
		return new NestedControl;
	}
}


test('signal routing to nested component', function () {
	$presenter = new class extends Application\UI\Presenter {
		protected function createComponentParent(): ParentControl
		{
			return new ParentControl;
		}


		public function renderDefault(): void
		{
			$this->terminate();
		}
	};

	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript, cookies: [Http\Helpers::StrictCookieName => 1]),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Test', 'GET', [
		'action' => 'default',
		'do' => 'parent-nested-refresh',
	]));

	$parent = $presenter['parent'];
	$nested = $parent['nested'];

	Assert::type(ParentControl::class, $parent);
	Assert::type(NestedControl::class, $nested);
	Assert::false($parent->signalCalled);
	Assert::true($nested->signalCalled);
});


class ParameterControl extends Application\UI\Control
{
	public ?int $receivedParam = null;


	public function handleClick(int $id): void
	{
		$this->receivedParam = $id;
	}
}


test('signal with parameters', function () {
	$presenter = new class extends Application\UI\Presenter {
		protected function createComponentParam(): ParameterControl
		{
			return new ParameterControl;
		}


		public function renderDefault(): void
		{
			$this->terminate();
		}
	};

	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript, cookies: [Http\Helpers::StrictCookieName => 1]),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Test', 'GET', [
		'action' => 'default',
		'do' => 'param-click',
		'param-id' => '42',
	]));

	$control = $presenter['param'];

	Assert::type(ParameterControl::class, $control);
	Assert::same(42, $control->receivedParam);
});


testException('invalid signal name throws exception', function () {
	$presenter = new class extends Application\UI\Presenter {
		public function renderDefault(): void
		{
			$this->terminate();
		}
	};

	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript, cookies: [Http\Helpers::StrictCookieName => 1]),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Test', 'GET', [
		'action' => 'default',
		'do' => 'nonexistent',
	]));
}, Application\UI\BadSignalException::class);


testException('signal to nonexistent component throws exception', function () {
	$presenter = new class extends Application\UI\Presenter {
		public function renderDefault(): void
		{
			$this->terminate();
		}
	};

	$presenter->injectPrimary(
		new Http\Request(new Http\UrlScript, cookies: [Http\Helpers::StrictCookieName => 1]),
		new Http\Response,
		new Application\PresenterFactory,
		new Application\Routers\SimpleRouter,
	);
	$presenter->autoCanonicalize = false;

	$presenter->run(new Application\Request('Test', 'GET', [
		'action' => 'default',
		'do' => 'nonexistent-click',
	]));
}, Application\UI\BadSignalException::class);
