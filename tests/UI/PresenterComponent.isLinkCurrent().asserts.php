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
	return callIsComponentLinkCurrent($presenter, $presenter, $request, $destination, $args);
}

function callIsComponentLinkCurrent(
	Application\UI\Presenter $presenter,
	Application\UI\PresenterComponent $component,
	Application\Request $request,
	$destination,
	array $args
) {
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

	return $component->isLinkCurrent($destination, $args);
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


// signal for nested component
$testPresenter = new TestPresenter;
$testControl = new TestControl;
$testPresenter['test'] = $testControl;
Assert::true(callIsComponentLinkCurrent(
	$testPresenter,
	$testControl,
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'test-click',
		'int' => 1,
		'bool' => TRUE,
	]),
	'click!',
	[]
));

$testPresenter = new TestPresenter;
$testControl = new TestControl;
$testPresenter['test'] = $testControl;
Assert::false(callIsComponentLinkCurrent(
	$testPresenter,
	$testControl,
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'test-click',
		'int' => 1,
		'bool' => TRUE,
	]),
	'otherSignal!',
	[]
));

$testPresenter = new TestPresenter;
$testControl = new TestControl;
$testPresenter['test'] = $testControl;
Assert::true(callIsComponentLinkCurrent(
	$testPresenter,
	$testControl,
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'test-click',
		'int' => 1,
		'bool' => TRUE,
		'test-x' => 1,
	]),
	'click!',
	[
		'x' => 1,
	]
));

$testPresenter = new TestPresenter;
$testControl = new TestControl;
$testPresenter['test'] = $testControl;
Assert::false(callIsComponentLinkCurrent(
	$testPresenter,
	$testControl,
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'test-click',
		'int' => 1,
		'bool' => TRUE,
		'test-x' => 1,
	]),
	'click!',
	[
		'x' => 2,
	]
));

$testPresenter = new TestPresenter;
$testControlWithAnotherTestControl = new TestControl;
$testPresenter['test'] = $testControlWithAnotherTestControl;
$testControlWithAnotherTestControl['test'] = new TestControl;
Assert::true(callIsComponentLinkCurrent(
	$testPresenter,
	$testControlWithAnotherTestControl,
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'test-test-click',
		'int' => 1,
		'bool' => TRUE,
		'test-test-x' => 1,
	]),
	'test:click!',
	[
		'x' => 1,
	]
));

$testPresenter = new TestPresenter;
$testControlWithAnotherTestControl = new TestControl;
$testPresenter['test'] = $testControlWithAnotherTestControl;
$testControlWithAnotherTestControl['test'] = new TestControl;
Assert::false(callIsComponentLinkCurrent(
	$testPresenter,
	$testControlWithAnotherTestControl,
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'test-test-click',
		'int' => 1,
		'bool' => TRUE,
		'test-test-x' => 1,
	]),
	'test:click!',
	[
		'x' => 2,
	]
));
