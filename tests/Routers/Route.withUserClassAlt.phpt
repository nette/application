<?php

/**
 * Test: Nette\Application\Routers\Route with WithUserClassAlt
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>/<id>', [
	'id' => [
		Route::Pattern => '\d{1,3}',
	],
]);

testRouteIn($route, '/presenter/12/', [
	'presenter' => 'Presenter',
	'id' => '12',
	'test' => 'testvalue',
], '/presenter/12?test=testvalue');

testRouteIn($route, '/presenter/1234');

testRouteIn($route, '/presenter/');
