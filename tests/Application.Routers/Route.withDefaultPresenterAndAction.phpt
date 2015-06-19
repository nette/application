<?php

/**
 * Test: Nette\Application\Routers\Route with WithDefaultPresenterAndAction
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<presenter>/<action>', [
	'presenter' => 'Default',
	'action' => 'default',
]);

testRouteIn($route, '/presenter/action/', 'Presenter', [
	'action' => 'action',
	'test' => 'testvalue',
], '/presenter/action?test=testvalue');

testRouteIn($route, '/default/default/', 'Default', [
	'action' => 'default',
	'test' => 'testvalue',
], '/?test=testvalue');

testRouteIn($route, '/presenter', 'Presenter', [
	'action' => 'default',
	'test' => 'testvalue',
], '/presenter/?test=testvalue');

testRouteIn($route, '/', 'Default', [
	'action' => 'default',
	'test' => 'testvalue',
], '/?test=testvalue');
