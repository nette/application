<?php declare(strict_types=1);

/**
 * Test: Nette\Application\Routers\Route with closure.
 */

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$closure = function () {};
$route = new Route('<id>', $closure);

testRouteIn($route, '/12', [
	'presenter' => 'Nette:Micro',
	'id' => '12',
	'test' => 'testvalue',
	'callback' => $closure,
], '/12?test=testvalue');
