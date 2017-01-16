<?php

/**
 * Test: Nette\Application\Routers\Route with CombinedUrlParam
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('extra<presenter>/<action>', [
	'presenter' => 'Default',
	'action' => 'default',
]);


testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/extrapresenter/action/', 'Presenter', [
	'action' => 'action',
	'test' => 'testvalue',
], '/extrapresenter/action?test=testvalue');

testRouteIn($route, '/extradefault/default/', 'Default', [
	'action' => 'default',
	'test' => 'testvalue',
], '/extra?test=testvalue');

testRouteIn($route, '/extra', 'Default', [
	'action' => 'default',
	'test' => 'testvalue',
], '/extra?test=testvalue');

testRouteIn($route, '/');
