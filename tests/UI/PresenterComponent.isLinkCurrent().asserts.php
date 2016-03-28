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
