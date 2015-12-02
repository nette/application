<?php

/**
 * Test: Nette\Application\Routers\Route and non-optional action.
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<action>', 'Default:');

testRouteIn($route, '/default', 'Default', [
	'action' => 'default',
	'test' => 'testvalue',
], '/default?test=testvalue');

testRouteIn($route, '/', NULL);


$route = new Route('<action>', 'Front:Default:');

testRouteIn($route, '/default', 'Front:Default', [
	'action' => 'default',
	'test' => 'testvalue',
], '/default?test=testvalue');
