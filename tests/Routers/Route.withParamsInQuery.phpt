<?php

/**
 * Test: Nette\Application\Routers\Route with WithParamsInQuery
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<action> ? <presenter>', [
	'presenter' => 'Default',
	'action' => 'default',
]);

testRouteIn($route, '/action/', [
	'presenter' => 'querypresenter',
	'action' => 'action',
	'test' => 'testvalue',
], '/action?presenter=querypresenter&test=testvalue');

testRouteIn($route, '/', [
	'presenter' => 'querypresenter',
	'action' => 'default',
	'test' => 'testvalue',
], '/?presenter=querypresenter&test=testvalue');
