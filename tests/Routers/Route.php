<?php

/**
 * Common code for Route test cases.
 */

use Tester\Assert;


function testRouteIn(Nette\Application\IRouter $route, $url, $expectedPresenter = NULL, $expectedParams = [], $expectedUrl = NULL)
{
	$url = new Nette\Http\UrlScript("http://example.com$url");
	$url->setScriptPath('/');
	$url->appendQuery([
		'test' => 'testvalue',
		'presenter' => 'querypresenter',
	]);

	$httpRequest = new Nette\Http\Request($url);

	$request = $route->match($httpRequest);

	if ($request) { // matched
		$params = $request->getParameters();
		asort($params);
		asort($expectedParams);
		Assert::same($expectedPresenter, $request->getPresenterName());
		Assert::same($expectedParams, $params);

		unset($params['extra']);
		$request->setParameters($params);
		$result = $route->constructUrl($request, $url);
		$result = $result && !strncmp($result, 'http://example.com', 18) ? substr($result, 18) : $result;
		Assert::same($expectedUrl, $result);

	} else { // not matched
		Assert::null($expectedPresenter);
	}
}


function testRouteOut(Nette\Application\IRouter $route, $presenter, $params = [])
{
	$url = new Nette\Http\Url('http://example.com');
	$request = new Nette\Application\Request($presenter, 'GET', $params);
	return $route->constructUrl($request, $url);
}
