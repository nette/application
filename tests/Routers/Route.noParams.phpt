<?php declare(strict_types=1);

/**
 * Test: Nette\Application\Routers\Route default usage.
 */

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('index.php', [
	'action' => 'default',
]);

testRouteIn($route, '/index.php', [
	'presenter' => 'querypresenter',
	'action' => 'default',
	'test' => 'testvalue',
], '/index.php?presenter=querypresenter&test=testvalue');

testRouteIn($route, '/');
