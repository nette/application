<?php

/**
 * Test: Nette\Application\Routers\RouteList & Route & module.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$list = new RouteList;
$list[] = new Route('auth/<presenter>[/<action>]', [
	'module' => 'Auth',
	'presenter' => 'Homepage',
	'action' => 'default',
]);
$list[] = new Route('<presenter>[/<action>]', [
	'module' => 'Default',
	'presenter' => 'Homepage',
	'action' => 'default',
]);

testRouteIn(
	$list,
	'/auth/',
	[
		'presenter' => 'Auth:Homepage',
		'action' => 'default',
		'test' => 'testvalue',
	],
	'/auth/?test=testvalue',
);
