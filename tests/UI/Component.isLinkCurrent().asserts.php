<?php

/**
 * Test: Nette\Application\UI\Component::isLinkCurrent()
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Http;
use Tester\Assert;


function callIsLinkCurrent(Application\Request $request, string $destination, array $args): bool
{
	$presenter = new TestPresenter;
	return callIsComponentLinkCurrent($presenter, $presenter, $request, $destination, $args);
}


function callIsComponentLinkCurrent(
	Application\UI\Presenter $presenter,
	Application\UI\Component $component,
	Application\Request $request,
	$destination,
	array $args
): bool {
	$url = new Http\UrlScript('http://localhost/index.php', '/index.php');
	$presenterFactory = Mockery::mock(Nette\Application\IPresenterFactory::class);
	$presenterFactory->shouldReceive('getPresenterClass')->andReturn('TestPresenter');

	$presenter->injectPrimary(
		null,
		$presenterFactory,
		new Application\Routers\SimpleRouter,
		new Http\Request($url),
		new Http\Response
	);
	$presenter->run($request);

	return $component->isLinkCurrent($destination, $args);
}


Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true]),
	'Test:default',
	[]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true]),
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
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true]),
	'Test:*',
	[]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true]),
	'Test:*',
	['float' => 1.0]
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true, 'float' => 1.0]),
	'Test:*',
	['float' => 1.0]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, ['int' => 1, 'bool' => true, 'float' => 1.0]),
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
		'bool' => true,
	]),
	'Test:default',
	[]
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
		'int' => 1,
		'bool' => true,
	]),
	'signal!',
	[]
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
		'int' => 1,
		'bool' => true,
	]),
	'otherSignal!',
	[]
));


// conflicting action in destination string and args
Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, [
		Application\UI\Presenter::ACTION_KEY => 'default',
		'int' => 1,
		'bool' => true,
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
		'bool' => true,
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
		'bool' => true,
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
		'bool' => true,
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
		'bool' => true,
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
		'bool' => true,
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
		'bool' => true,
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
		'bool' => true,
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
		'bool' => true,
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
		'bool' => true,
		'test-test-x' => 1,
	]),
	'test:click!',
	[
		'x' => 2,
	]
));
