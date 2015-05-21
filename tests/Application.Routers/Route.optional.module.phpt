<?php

/**
 * Test: Nette\Application\Routers\Route with module in optional sequence.
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('[<module admin|image>/]<presenter>/<action>', [
	'module' => 'Front',
	'presenter' => 'Homepage',
	'action' => 'default',
]);

testRouteIn($route, '/one', 'Front:One', [
	'action' => 'default',
	'test' => 'testvalue',
], '/one/?test=testvalue');

testRouteIn($route, '/admin/one', 'Admin:One', [
	'action' => 'default',
	'test' => 'testvalue',
], '/admin/one/?test=testvalue');

testRouteIn($route, '/one/admin', 'Front:One', [
	'action' => 'admin',
	'test' => 'testvalue',
], '/one/admin?test=testvalue');
