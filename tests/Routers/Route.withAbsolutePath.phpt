<?php

/**
 * Test: Nette\Application\Routers\Route with WithAbsolutePath
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('/<abspath>/', [
	'presenter' => 'Default',
	'action' => 'default',
]);

testRouteIn($route, '/abc', 'Default', [
	'abspath' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc/?test=testvalue');
