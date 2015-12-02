<?php

/**
 * Test: Nette\Application\Routers\Route with CamelcapsVsDash
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


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
