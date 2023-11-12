<?php

/**
 * Test: Nette\Application\Routers\Route with FilterIn & FilterOut
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route(' ? action=<presenter>', [
	'presenter' => [
		Route::FilterIn => fn($s) => strrev($s),
		Route::FilterOut => fn($s) => strtoupper(strrev($s)),
	],
]);

testRouteIn($route, '/?action=abc', [
	'presenter' => 'cba',
	'test' => 'testvalue',
], '/?action=ABC&test=testvalue');
