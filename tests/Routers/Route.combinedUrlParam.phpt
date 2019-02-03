<?php

/**
 * Test: Nette\Application\Routers\Route with CombinedUrlParam
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('extra<presenter>/<action>', [
	'presenter' => 'Default',
	'action' => 'default',
]);


testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/extrapresenter/action/', [
	'presenter' => 'Presenter',
	'action' => 'action',
	'test' => 'testvalue',
], '/extrapresenter/action?test=testvalue');

testRouteIn($route, '/extradefault/default/', [
	'presenter' => 'Default',
	'action' => 'default',
	'test' => 'testvalue',
], '/extra?test=testvalue');

testRouteIn($route, '/extra', [
	'presenter' => 'Default',
	'action' => 'default',
	'test' => 'testvalue',
], '/extra?test=testvalue');

testRouteIn($route, '/');
