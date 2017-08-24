<?php

/**
 * Test: Nette\Application\Routers\Route: required parameter with default value
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>/<default=123>/<required>', [
	'action' => 'default',
]);

testRouteIn($route, '/presenter/');
testRouteIn($route, '/presenter/abc');
testRouteIn($route, '/presenter/abc/');

testRouteIn($route, '/presenter/abc/xyy', 'Presenter', [
	'default' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
	'required' => 'xyy',
], '/presenter/abc/xyy?test=testvalue');


Assert::null(testRouteOut($route, 'Homepage'));
Assert::null(testRouteOut($route, 'Homepage', ['default' => 'abc']));

Assert::same(
	'http://example.com/homepage/123/xyz',
	testRouteOut($route, 'Homepage', ['required' => 'xyz'])
);

Assert::same(
	'http://example.com/homepage/abc/xyz',
	testRouteOut($route, 'Homepage', ['required' => 'xyz', 'default' => 'abc'])
);
