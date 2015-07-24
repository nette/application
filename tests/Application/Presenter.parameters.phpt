<?php

/**
 * Test: Nette\Application\UI\Presenter::initGlobalParameters() and signals
 */

use Nette\Http;
use Nette\Application;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	public $ajax = FALSE;


	public function isAjax()
	{
		return $this->ajax;
	}


	protected function startup()
	{
		parent::startup();
		throw new Application\AbortException();
	}


}


function createPresenter()
{
	$presenter = new TestPresenter();
	$presenter->injectPrimary(NULL, NULL, NULL, new Http\Request(new Http\UrlScript()), new Http\Response());
	$presenter->autoCanonicalize = FALSE;
	return $presenter;
}


test(function () {
	//signal in GET
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'GET', [
		'do' => 'foo'
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test(function () {
	//signal for subcomponent in GET
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'GET', [
		'do' => 'foo-bar'
	]));
	Assert::same(['foo', 'bar'], $presenter->getSignal());
});

test(function () {
	//signal in POST
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', [], [
		'do' => 'foo'
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test(function () {
	//signal in POST overwriting empty GET
	$presenter = createPresenter();
	$presenter->run(new Application\Request('Foo', 'POST', ['do' => NULL], [
		'do' => 'foo'
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});

test(function () {
	//AJAX: signal in POST
	$presenter = createPresenter();
	$presenter->ajax = TRUE;
	$presenter->run(new Application\Request('Foo', 'POST', [], [
		'do' => 'foo'
	]));
	Assert::same(['', 'foo'], $presenter->getSignal());
});
