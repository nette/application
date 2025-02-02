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
	$presenter->injectPrimary(new Http\Request(new Http\UrlScript), new Http\Response);
	$presenter->autoCanonicalize = false;
	return $presenter;
}


test('signal parsing from GET parameters', function () {
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'GET', [
		'do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test('compound signal name handling', function () {
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'GET', [
		'do' => 'foo-bar',
	]));
	Assert::same(['foo', 'bar'], $presenter->getSignal());
});

test('POST signal without CSRF protection', function () {
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', [], [
		'do' => 'foo',
	]));
	Assert::null($presenter->getSignal());
});

test('POST signal with CSRF protection', function () {
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', [], [
		'_do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test('conflicting signal parameters resolution', function () {
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => null], [
		'do' => 'foo',
	]));
	Assert::null($presenter->getSignal());
});

test('POST signal priority over GET', function () {
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => 'bar'], [
		'_do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test('AJAX signal handling in POST', function () {
	$presenter = createPresenter();
	$presenter->ajax = true;
	$presenter->run(new Application\Request('Foo', 'POST', [], [
		'do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test('AJAX signal override in POST data', function () {
	$presenter = createPresenter();
	$presenter->ajax = true;
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => 'bar'], [
		'do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test('AJAX with explicit CSRF-protected signal', function () {
	$presenter = createPresenter();
	$presenter->ajax = true;
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => 'bar'], [
		'_do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});
