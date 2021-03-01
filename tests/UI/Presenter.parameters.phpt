<?php

/**
 * Test: Nette\Application\UI\Presenter::initGlobalParameters() and signals
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	public $ajax = false;


	public function isAjax(): bool
	{
		return $this->ajax;
	}


	protected function startup(): void
	{
		parent::startup();
		throw new Application\AbortException;
	}
}


function createPresenter()
{
	$presenter = new TestPresenter;
	$presenter->injectPrimary(null, null, null, new Http\Request(new Http\UrlScript), new Http\Response);
	$presenter->autoCanonicalize = false;
	return $presenter;
}


test('signal in GET', function () {
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'GET', [
		'do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test('signal for subcomponent in GET', function () {
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'GET', [
		'do' => 'foo-bar',
	]));
	Assert::same(['foo', 'bar'], $presenter->getSignal());
});

test('signal in POST', function () {
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', [], [
		'do' => 'foo',
	]));
	Assert::null($presenter->getSignal());
});

test('_signal_ in POST', function () {
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', [], [
		'_do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test('signal in POST not overwriting GET', function () {
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => null], [
		'do' => 'foo',
	]));
	Assert::null($presenter->getSignal());
});

test('_signal_ in POST overwriting GET', function () {
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => 'bar'], [
		'_do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test('AJAX: signal in POST', function () {
	$presenter = createPresenter();
	$presenter->ajax = true;
	$presenter->run(new Application\Request('Foo', 'POST', [], [
		'do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test('AJAX: signal in POST overwriting GET', function () {
	$presenter = createPresenter();
	$presenter->ajax = true;
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => 'bar'], [
		'do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test('AJAX: _signal_ in POST overwriting GET', function () {
	$presenter = createPresenter();
	$presenter->ajax = true;
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => 'bar'], [
		'_do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});
