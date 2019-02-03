<?php

/**
 * Test: Nette\Application\Routers\Route with WithDefaultPresenterAndAction
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>/<action>', [
	'presenter' => 'Default',
	'action' => 'default',
]);

testRouteIn($route, '/presenter/action/', [
	'presenter' => 'Presenter',
	'action' => 'action',
	'test' => 'testvalue',
], '/presenter/action?test=testvalue');

testRouteIn($route, '/default/default/', [
	'presenter' => 'Default',
	'action' => 'default',
	'test' => 'testvalue',
], '/?test=testvalue');

testRouteIn($route, '/presenter', [
	'presenter' => 'Presenter',
	'action' => 'default',
	'test' => 'testvalue',
], '/presenter/?test=testvalue');

testRouteIn($route, '/', [
	'presenter' => 'Default',
	'action' => 'default',
	'test' => 'testvalue',
], '/?test=testvalue');
