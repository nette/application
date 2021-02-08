<?php

/**
 * Test: Nette\Application\Routers\Route: required parameter with default value
 */

declare(strict_types=1);

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

testRouteIn($route, '/presenter/abc/xyy', [
	'presenter' => 'Presenter',
	'default' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
	'required' => 'xyy',
], '/presenter/abc/xyy?test=testvalue');


Assert::null(testRouteOut($route, ['presenter' => 'Homepage']));
Assert::null(testRouteOut($route, ['presenter' => 'Homepage', 'default' => 'abc']));

Assert::same(
	'http://example.com/homepage/123/xyz',
	testRouteOut($route, ['presenter' => 'Homepage', 'action' => 'default', 'required' => 'xyz'])
);

Assert::same(
	'http://example.com/homepage/abc/xyz',
	testRouteOut($route, ['presenter' => 'Homepage', 'action' => 'default', 'required' => 'xyz', 'default' => 'abc'])
);
