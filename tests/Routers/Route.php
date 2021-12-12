<?php

/**
 * Common code for Route test cases.
 */

declare(strict_types=1);

use Tester\Assert;


function testRouteIn(Nette\Routing\Router $route, string $url, ?array $expectedParams = null, ?string $expectedUrl = null): void
{
	$urlBuilder = new Nette\Http\Url("http://example.com$url");
	$urlBuilder->appendQuery([
		'test' => 'testvalue',
		'presenter' => 'querypresenter',
	]);
	$url = new Nette\Http\UrlScript($urlBuilder, '/');

	$httpRequest = new Nette\Http\Request($url);

	$params = $route->match($httpRequest);

	if ($params === null) { // not matched
		Assert::null($expectedParams);

	} else { // matched
		asort($params);
		asort($expectedParams);
		Assert::same($expectedParams, $params);

		unset($params['extra']);
		$result = $route->constructUrl($params, $url);
		$result = $result && !strncmp($result, 'http://example.com', 18)
			? substr($result, 18)
			: $result;
		Assert::same($expectedUrl, $result);
	}
}


function testRouteOut(Nette\Routing\Router $route, array $params = []): ?string
{
	$url = new Nette\Http\UrlScript('http://example.com');
	return $route->constructUrl($params, $url);
}
