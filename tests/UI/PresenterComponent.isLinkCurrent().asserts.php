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
	new Application\Request('Test', Http\Request::GET, []),
	'Test:default',
	[]
));
Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1]),
	'Test:default',
	[]
));
Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1]),
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
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true]),
	'Test:default',
	[]
));
Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true]),
	'Test:default',
	['bool' => true]
));
Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true]),
	'Test:default',
	[
		'bool' => true,
		'int' => 1,
	]
));
Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true]),
	'Test:default',
	[
		'bool' => false,
		'int' => 1,
	]
));
Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true]),
	'Test:default',
	[
		'bool' => false,
		'int' => 2,
	]
));
Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true, Application\UI\Presenter::ACTION_KEY => 'otherAction']),
	'Test:default',
	[
		'bool' => true,
		'int' => 1,
	]
));
Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true, Application\UI\Presenter::ACTION_KEY => 'otherAction']),
	'Test:otherAction',
	[
		'bool' => true,
		'int' => 1,
	]
));
Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, []),
	'Test:*',
	[]
));
Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1]),
	'Test:*',
	[]
));
Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET),
	'Test:*',
	['int' => 1]
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
	new Application\Request('Test', Http\Request::GET, [Application\UI\Presenter::SIGNAL_KEY => 'signal']),
	'Test:default',
	[]
));
Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [Application\UI\Presenter::SIGNAL_KEY => 'signal']),
	'signal!',
	[]
));
Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [Application\UI\Presenter::SIGNAL_KEY => 'signal']),
	'otherSignal!',
	[]
));
