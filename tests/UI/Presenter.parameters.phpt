<?php

/**
 * Test: Nette\Application\UI\Presenter::initGlobalParameters() and signals
 */

use Nette\Application;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	public $ajax = false;


	public function isAjax()
	{
		return $this->ajax;
	}


	protected function startup()
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


test(function () {
	//signal in GET
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'GET', [
		'do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test(function () {
	//signal for subcomponent in GET
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'GET', [
		'do' => 'foo-bar',
	]));
	Assert::same(['foo', 'bar'], $presenter->getSignal());
});

test(function () {
	//signal in POST
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', [], [
		'do' => 'foo',
	]));
	Assert::null($presenter->getSignal());
});

test(function () {
	//_signal_ in POST
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', [], [
		'_do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test(function () {
	//signal in POST not overwriting GET
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => null], [
		'do' => 'foo',
	]));
	Assert::null($presenter->getSignal());
});

test(function () {
	//_signal_ in POST overwriting GET
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => 'bar'], [
		'_do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test(function () {
	//AJAX: signal in POST
	$presenter = createPresenter();
	$presenter->ajax = true;
	$presenter->run(new Application\Request('Foo', 'POST', [], [
		'do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test(function () {
	//AJAX: signal in POST overwriting GET
	$presenter = createPresenter();
	$presenter->ajax = true;
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => 'bar'], [
		'do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test(function () {
	//AJAX: _signal_ in POST overwriting GET
	$presenter = createPresenter();
	$presenter->ajax = true;
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => 'bar'], [
		'_do' => 'foo',
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});
