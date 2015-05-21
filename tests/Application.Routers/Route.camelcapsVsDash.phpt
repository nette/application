<?php

/**
 * Test: Nette\Application\Routers\Route with CamelcapsVsDash
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<presenter>', [
	'presenter' => 'DefaultPresenter',
]);

testRouteIn($route, '/abc-x-y-z', 'AbcXYZ', [
	'test' => 'testvalue',
], '/abc-x-y-z?test=testvalue');

testRouteIn($route, '/', 'DefaultPresenter', [
	'test' => 'testvalue',
], '/?test=testvalue');

testRouteIn($route, '/--');
