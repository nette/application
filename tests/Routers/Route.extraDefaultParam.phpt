<?php

/**
 * Test: Nette\Application\Routers\Route with ExtraDefaultParam
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>/<action>/<id \d{1,3}>/', [
	'extra' => null,
]);

Assert::same(['extra' => null], $route->getConstantParameters());

testRouteIn($route, '/presenter/action/12/any');

testRouteIn($route, '/presenter/action/12', [
	'presenter' => 'Presenter',
	'action' => 'action',
	'id' => '12',
	'extra' => null,
	'test' => 'testvalue',
], '/presenter/action/12/?test=testvalue');

testRouteIn($route, '/presenter/action/1234');

testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/presenter');

testRouteIn($route, '/');
