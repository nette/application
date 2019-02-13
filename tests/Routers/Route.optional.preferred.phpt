<?php

/**
 * Test: Nette\Application\Routers\Route with 'required' optional sequence.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('index[!.html]', [
]);

testRouteIn($route, '/index.html', [
	'presenter' => 'querypresenter',
	'test' => 'testvalue',
], '/index.html?presenter=querypresenter&test=testvalue');

testRouteIn($route, '/index', [
	'presenter' => 'querypresenter',
	'test' => 'testvalue',
], '/index.html?presenter=querypresenter&test=testvalue');
