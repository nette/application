<?php

/**
 * Test: Nette\Application\Routers\Route with Secured
 */

use Nette\Application\Routers\Route;
use Nette\Application\Request;
use Nette\Http\Url;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<param>', [
	'presenter' => 'Presenter',
]);

$url = $route->constructUrl(
	new Request('Presenter', NULL, ['param' => 'any']),
	new Url('https://example.org')
);
Assert::same('http://example.org/any', $url);


$route = new Route('<param>', [
	'presenter' => 'Presenter',
], Route::SECURED);

testRouteIn($route, '/any', 'Presenter', [
	'param' => 'any',
	'test' => 'testvalue',
], 'https://example.com/any?test=testvalue');

$url = $route->constructUrl(
	new Request('Presenter', NULL, ['param' => 'any']),
	new Url('http://example.org')
);
Assert::same('https://example.org/any', $url);
