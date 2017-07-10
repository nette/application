<?php

/**
 * Test: Nette\Application\Routers\Route with NoDefaultParams
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>/<action>/<extra>', [
]);

testRouteIn($route, '/presenter/action/12', 'Presenter', [
	'action' => 'action',
	'extra' => '12',
	'test' => 'testvalue',
], NULL);
