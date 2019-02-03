<?php

/**
 * Test: Nette\Application\Routers\Route with WithNamedParamsInQuery
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('?action=<presenter> & act=<action [a-z]+>', [
	'presenter' => 'Default',
	'action' => 'default',
]);

testRouteIn($route, '/?act=action', [
	'presenter' => 'Default',
	'action' => 'action',
	'test' => 'testvalue',
], '/?act=action&test=testvalue');

testRouteIn($route, '/?act=default', [
	'presenter' => 'Default',
	'action' => 'default',
	'test' => 'testvalue',
], '/?test=testvalue');
