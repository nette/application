<?php

/**
 * Test: Nette\Application\Routers\Route with closure.
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$closure = function() {};
$route = new Route('<id>', $closure);

testRouteIn($route, '/12', 'Nette:Micro', [
	'id' => '12',
	'test' => 'testvalue',
	'callback' => $closure,
], '/12?test=testvalue');
