<?php declare(strict_types=1);

/**
 * Test: Nette\Application\Routers\Route UTF-8 parameter.
 */

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<param č>', [
	'presenter' => 'Default',
]);

testRouteIn($route, '/č', [
	'presenter' => 'Default',
	'param' => 'č',
	'test' => 'testvalue',
], '/%C4%8D?test=testvalue');

testRouteIn($route, '/%C4%8D', [
	'presenter' => 'Default',
	'param' => 'č',
	'test' => 'testvalue',
], '/%C4%8D?test=testvalue');

testRouteIn($route, '/');
