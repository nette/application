<?php

/**
 * Test: Nette\Application\Routers\Route with ExtraDefaultParam
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<presenter>/<action>/<id \d{1,3}>/', [
	'extra' => NULL,
]);

testRouteIn($route, '/presenter/action/12/any');

testRouteIn($route, '/presenter/action/12', 'Presenter', [
	'action' => 'action',
	'id' => '12',
	'extra' => NULL,
	'test' => 'testvalue',
], '/presenter/action/12/?test=testvalue');

testRouteIn($route, '/presenter/action/1234');

testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/presenter');

testRouteIn($route, '/');
