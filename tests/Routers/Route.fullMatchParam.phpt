<?php

/**
 * Test: Nette\Application\Routers\Route and full match parameter.
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<param .+>', [
	'presenter' => 'Default',
]);

testRouteIn($route, '/one', 'Default', [
	'param' => 'one',
	'test' => 'testvalue',
], '/one?test=testvalue');

testRouteIn($route, '/');
