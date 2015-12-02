<?php

/**
 * Test: Nette\Application\Routers\Route with FILTER_IN & FILTER_OUT
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>', [
	'presenter' => [
		Route::FILTER_IN => function ($s) {
			return strrev($s);
		},
		Route::FILTER_OUT => function ($s) {
			return strrev($s);
		},
	],
]);

testRouteIn($route, '/abc/', 'cba', [
	'test' => 'testvalue',
], '/abc?test=testvalue');
