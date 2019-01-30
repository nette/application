<?php

/**
 * Test: Nette\Application\Routers\Route auto-optional sequence.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>/<action=default>[/<id>]');

testRouteIn($route, '/presenter/action/12/any');

testRouteIn($route, '/presenter/action/12', 'Presenter', [
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
], '/presenter/action/12?test=testvalue');

testRouteIn($route, '/presenter/action/', 'Presenter', [
	'action' => 'action',
	'id' => null,
	'test' => 'testvalue',
], '/presenter/action?test=testvalue');

testRouteIn($route, '/presenter/action', 'Presenter', [
	'action' => 'action',
	'id' => null,
	'test' => 'testvalue',
], '/presenter/action?test=testvalue');

testRouteIn($route, '/presenter/', 'Presenter', [
	'id' => null,
	'action' => 'default',
	'test' => 'testvalue',
], '/presenter/?test=testvalue');

testRouteIn($route, '/presenter', 'Presenter', [
	'id' => null,
	'action' => 'default',
	'test' => 'testvalue',
], '/presenter/?test=testvalue');

testRouteIn($route, '/');
