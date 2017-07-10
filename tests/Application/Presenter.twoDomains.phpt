<?php

/**
 * Test: Nette\Application\UI\Presenter::link()
 */

use Nette\Application;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	protected function createTemplate($class = null)
	{
	}
}


function testLink($domain)
{
	$url = new Http\UrlScript('http://' . $domain . '/index.php');
	$url->setScriptPath('/index.php');

	$presenter = new TestPresenter;
	$presenter->injectPrimary(
		null,
		Mockery::mock(Nette\Application\IPresenterFactory::class),
		new Application\Routers\SimpleRouter,
		new Http\Request($url),
		new Http\Response
	);

	$request = new Application\Request('Test', Http\Request::GET, []);
	$presenter->run($request);

	Assert::same( 'http://' . $domain . '/index.php?action=default&presenter=Test', $presenter->link('//this') );
}


testLink('first.localhost');
testLink('second.localhost');
