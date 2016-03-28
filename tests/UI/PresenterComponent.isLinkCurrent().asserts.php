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
	new Application\Request('Test', Http\Request::GET, array('int' => 1, 'bool' => TRUE)),
	'Test:default',
	array()
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 1, 'bool' => TRUE)),
	'Test:default',
	array('int' => 2)
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array(Application\UI\Presenter::ACTION_KEY => 'otherAction')),
	'Test:default',
	array()
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array(Application\UI\Presenter::ACTION_KEY => 'otherAction')),
	'Test:otherAction',
	array()
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 1, 'bool' => TRUE)),
	'Test:default',
	array('bool' => TRUE)
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 1, 'bool' => TRUE)),
	'Test:default',
	array(
		'bool' => TRUE,
		'int' => 1,
	)
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 1, 'bool' => TRUE)),
	'Test:default',
	array(
		'bool' => FALSE,
		'int' => 1,
	)
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 1, 'bool' => TRUE)),
	'Test:default',
	array(
		'bool' => FALSE,
		'int' => 2,
	)
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 1, 'bool' => TRUE, Application\UI\Presenter::ACTION_KEY => 'otherAction')),
	'Test:default',
	array(
		'bool' => TRUE,
		'int' => 1,
	)
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 1, 'bool' => TRUE, Application\UI\Presenter::ACTION_KEY => 'otherAction')),
	'Test:otherAction',
	array(
		'bool' => TRUE,
		'int' => 1,
	)
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 1, 'bool' => TRUE)),
	'Test:*',
	array()
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 1, 'bool' => TRUE)),
	'Test:*',
	array('float' => 1.0)
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 1, 'bool' => TRUE, 'float' => 1.0)),
	'Test:*',
	array('float' => 1.0)
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 1, 'bool' => TRUE, 'float' => 1.0)),
	'Test:*',
	array('float' => 2.0)
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 1, Application\UI\Presenter::ACTION_KEY => 'otherAction')),
	'Test:*',
	array(
		'int' => 1,
	)
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array('int' => 2, Application\UI\Presenter::ACTION_KEY => 'otherAction')),
	'Test:*',
	array(
		'int' => 1,
	)
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
		'int' => 1,
		'bool' => TRUE,
	)),
	'Test:default',
	array()
));

Assert::true(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
		'int' => 1,
		'bool' => TRUE,
	)),
	'signal!',
	array()
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
		'int' => 1,
		'bool' => TRUE,
	)),
	'otherSignal!',
	array()
));


// conflicting action in destination string and args
Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::ACTION_KEY => 'default',
		'int' => 1,
		'bool' => TRUE,
	)),
	'Test:default',
	array(
		Application\UI\Presenter::ACTION_KEY => 'otherAction',
	)
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::ACTION_KEY => 'default',
		'int' => 1,
		'bool' => TRUE,
	)),
	'Test:otherAction',
	array(
		Application\UI\Presenter::ACTION_KEY => 'default',
	)
));


// conflicting signal in destination string and args
Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
		'int' => 1,
		'bool' => TRUE,
	)),
	'signal!',
	array(
		Application\UI\Presenter::SIGNAL_KEY => 'otherSignal',
	)
));

Assert::false(callIsLinkCurrent(
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
		'int' => 1,
		'bool' => TRUE,
	)),
	'otherSignal!',
	array(
		Application\UI\Presenter::SIGNAL_KEY => 'signal',
	)
));


// signal for nested component
$testPresenter = new TestPresenter;
$testControl = new TestControl;
$testPresenter['test'] = $testControl;
Assert::true(callIsComponentLinkCurrent(
	$testPresenter,
	$testControl,
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::SIGNAL_KEY => 'test-click',
		'int' => 1,
		'bool' => TRUE,
	)),
	'click!',
	array()
));

$testPresenter = new TestPresenter;
$testControl = new TestControl;
$testPresenter['test'] = $testControl;
Assert::false(callIsComponentLinkCurrent(
	$testPresenter,
	$testControl,
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::SIGNAL_KEY => 'test-click',
		'int' => 1,
		'bool' => TRUE,
	)),
	'otherSignal!',
	array()
));

$testPresenter = new TestPresenter;
$testControl = new TestControl;
$testPresenter['test'] = $testControl;
Assert::true(callIsComponentLinkCurrent(
	$testPresenter,
	$testControl,
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::SIGNAL_KEY => 'test-click',
		'int' => 1,
		'bool' => TRUE,
		'test-x' => 1,
	)),
	'click!',
	array(
		'x' => 1,
	)
));

$testPresenter = new TestPresenter;
$testControl = new TestControl;
$testPresenter['test'] = $testControl;
Assert::false(callIsComponentLinkCurrent(
	$testPresenter,
	$testControl,
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::SIGNAL_KEY => 'test-click',
		'int' => 1,
		'bool' => TRUE,
		'test-x' => 1,
	)),
	'click!',
	array(
		'x' => 2,
	)
));

$testPresenter = new TestPresenter;
$testControlWithAnotherTestControl = new TestControl;
$testPresenter['test'] = $testControlWithAnotherTestControl;
$testControlWithAnotherTestControl['test'] = new TestControl;
Assert::true(callIsComponentLinkCurrent(
	$testPresenter,
	$testControlWithAnotherTestControl,
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::SIGNAL_KEY => 'test-test-click',
		'int' => 1,
		'bool' => TRUE,
		'test-test-x' => 1,
	)),
	'test:click!',
	array(
		'x' => 1,
	)
));

$testPresenter = new TestPresenter;
$testControlWithAnotherTestControl = new TestControl;
$testPresenter['test'] = $testControlWithAnotherTestControl;
$testControlWithAnotherTestControl['test'] = new TestControl;
Assert::false(callIsComponentLinkCurrent(
	$testPresenter,
	$testControlWithAnotherTestControl,
	new Application\Request('Test', Http\Request::GET, array(
		Application\UI\Presenter::SIGNAL_KEY => 'test-test-click',
		'int' => 1,
		'bool' => TRUE,
		'test-test-x' => 1,
	)),
	'test:click!',
	array(
		'x' => 2,
	)
));
