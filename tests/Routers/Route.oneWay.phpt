<?php

/**
 * Test: Nette\Application\Routers\Route with OneWay
 */

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>/<action>', [
	'presenter' => 'Default',
	'action' => 'default',
], Route::ONE_WAY);

testRouteIn($route, '/presenter/action/', 'Presenter', [
	'action' => 'action',
	'test' => 'testvalue',
], NULL);
