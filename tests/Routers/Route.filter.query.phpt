<?php

/**
 * Test: Nette\Application\Routers\Route with FILTER_IN & FILTER_OUT
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route(' ? action=<presenter>', [
	'presenter' => [
		Route::FILTER_IN => function ($s) {
			return strrev($s);
		},
		Route::FILTER_OUT => function ($s) {
			return strtoupper(strrev($s));
		},
	],
]);

testRouteIn($route, '/?action=abc', 'cba', [
	'test' => 'testvalue',
], '/?test=testvalue&action=ABC');
