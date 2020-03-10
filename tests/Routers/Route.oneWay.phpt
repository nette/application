<?php

/**
 * Test: Nette\Application\Routers\Route with OneWay
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


@$route = new Route('<presenter>/<action>', [ // @ is deprecated
	'presenter' => 'Default',
	'action' => 'default',
], Route::ONE_WAY);

testRouteIn($route, '/presenter/action/', [
	'presenter' => 'Presenter',
	'action' => 'action',
	'test' => 'testvalue',
], null);
