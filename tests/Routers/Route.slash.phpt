<?php

/**
 * Test: Nette\Application\Routers\Route with slash in path.
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<param>', [
	'presenter' => 'Presenter',
]);

testRouteIn($route, '/a/b');
Assert::null(testRouteOut($route, 'Presenter', ['param' => 'a/b']));


$route = new Route('<param .+>', [
	'presenter' => 'Presenter',
]);

testRouteIn($route, '/a/b', 'Presenter', [
	'param' => 'a/b',
	'test' => 'testvalue',
], '/a/b?test=testvalue');
