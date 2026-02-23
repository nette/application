<?php declare(strict_types=1);

/**
 * Test: Nette\Application\Routers\Route with WithAbsolutePath
 */

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('/<abspath>/', [
	'presenter' => 'Default',
	'action' => 'default',
]);

testRouteIn($route, '/abc', [
	'presenter' => 'Default',
	'abspath' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc/?test=testvalue');
