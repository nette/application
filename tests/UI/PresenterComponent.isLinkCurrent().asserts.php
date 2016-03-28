<?php

/**
 * Test: Nette\Application\UI\PresenterComponent::isLinkCurrent()
 */

use Nette\Application;
use Nette\Http;
use Tester\Assert;

function callIsLinkCurrent(Application\Request $request, $destination, array $args)
{
	$presenter = new TestPresenter;

	$url = new Http\UrlScript('http://localhost/index.php');
	$url->setScriptPath('/index.php');

	$presenter->injectPrimary(
		NULL,
		new MockPresenterFactory,
		new Application\Routers\SimpleRouter,
		new Http\Request($url),
		new Http\Response
	);
	$presenter->run($request);

	return $presenter->isLinkCurrent($destination, $args);
}

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => TRUE]),
	'Test:default',
	[]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => TRUE]),
	'Test:default',
	['int' => 2]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [Application\UI\Presenter::ACTION_KEY => 'otherAction']),
	'Test:default',
	[]
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [Application\UI\Presenter::ACTION_KEY => 'otherAction']),
	'Test:otherAction',
	[]
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => TRUE]),
	'Test:default',
	['bool' => TRUE]
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => TRUE]),
	'Test:default',
	[
		'bool' => TRUE,
		'int' => 1,
	]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => TRUE]),
	'Test:default',
	[
		'bool' => FALSE,
		'int' => 1,
	]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => TRUE]),
	'Test:default',
	[
		'bool' => FALSE,
		'int' => 2,
	]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => TRUE, Application\UI\Presenter::ACTION_KEY => 'otherAction']),
	'Test:default',
	[
		'bool' => TRUE,
		'int' => 1,
	]
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => TRUE, Application\UI\Presenter::ACTION_KEY => 'otherAction']),
	'Test:otherAction',
	[
		'bool' => TRUE,
		'int' => 1,
	]
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => TRUE]),
	'Test:*',
	[]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => TRUE]),
	'Test:*',
	['float' => 1.0]
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => TRUE, 'float' => 1.0]),
	'Test:*',
	['float' => 1.0]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => TRUE, 'float' => 1.0]),
	'Test:*',
	['float' => 2.0]
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, Application\UI\Presenter::ACTION_KEY => 'otherAction']),
	'Test:*',
	[
		'int' => 1,
	]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 2, Application\UI\Presenter::ACTION_KEY => 'otherAction']),
	'Test:*',
	[
		'int' => 1,
	]
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
		'int' => 1,
		'bool' => TRUE,
	]),
	'Test:default',
	[]
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
		'int' => 1,
		'bool' => TRUE,
	]),
	'signal!',
	[]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
		'int' => 1,
		'bool' => TRUE,
	]),
	'otherSignal!',
	[]
));


// conflicting action in destination string and args
Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::ACTION_KEY => 'default',
		'int' => 1,
		'bool' => TRUE,
	]),
	'Test:default',
	[
		Application\UI\Presenter::ACTION_KEY => 'otherAction',
	]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::ACTION_KEY => 'default',
		'int' => 1,
		'bool' => TRUE,
	]),
	'Test:otherAction',
	[
		Application\UI\Presenter::ACTION_KEY => 'default',
	]
));


// conflicting signal in destination string and args
Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
		'int' => 1,
		'bool' => TRUE,
	]),
	'signal!',
	[
		Application\UI\Presenter::SIGNAL_KEY => 'otherSignal',
	]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
		'int' => 1,
		'bool' => TRUE,
	]),
	'otherSignal!',
	[
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
	]
));
