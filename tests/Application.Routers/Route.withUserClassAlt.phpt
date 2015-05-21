<?php

/**
 * Test: Nette\Application\Routers\Route with WithUserClassAlt
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<presenter>/<id>', [
	'id' => [
		Route::PATTERN => '\d{1,3}',
	],
]);

testRouteIn($route, '/presenter/12/', 'Presenter', [
	'id' => '12',
	'test' => 'testvalue',
], '/presenter/12?test=testvalue');

testRouteIn($route, '/presenter/1234');

testRouteIn($route, '/presenter/');
