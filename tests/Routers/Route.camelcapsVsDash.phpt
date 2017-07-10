<?php

/**
 * Test: Nette\Application\Routers\Route with CamelcapsVsDash
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>', [
	'presenter' => 'DefaultPresenter',
]);

testRouteIn($route, '/abc-x-y-z', 'AbcXYZ', [
	'test' => 'testvalue',
], '/abc-x-y-z?test=testvalue');

testRouteIn($route, '/', 'DefaultPresenter', [
	'test' => 'testvalue',
], '/?test=testvalue');

testRouteIn($route, '/--');
