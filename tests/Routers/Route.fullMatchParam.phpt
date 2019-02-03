<?php

/**
 * Test: Nette\Application\Routers\Route and full match parameter.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<param .+>', [
	'presenter' => 'Default',
]);

testRouteIn($route, '/one', [
	'presenter' => 'Default',
	'param' => 'one',
	'test' => 'testvalue',
], '/one?test=testvalue');

testRouteIn($route, '/');
