<?php

/**
 * Test: Nette\Application\Routers\Route with WithAbsolutePath
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('/<abspath>/', [
	'presenter' => 'Default',
	'action' => 'default',
]);

testRouteIn($route, '/abc', 'Default', [
	'abspath' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc/?test=testvalue');
