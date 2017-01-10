<?php

/**
 * Test: Nette\Application\Routers\Route UTF-8 parameter.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<param č>', [
	'presenter' => 'Default',
]);

testRouteIn($route, '/č', 'Default', [
	'param' => 'č',
	'test' => 'testvalue',
], '/%C4%8D?test=testvalue');

testRouteIn($route, '/%C4%8D', 'Default', [
	'param' => 'č',
	'test' => 'testvalue',
], '/%C4%8D?test=testvalue');

testRouteIn($route, '/');
