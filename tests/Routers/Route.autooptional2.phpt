<?php

/**
 * Test: Nette\Application\Routers\Route auto-optional sequence.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>/<action=default>/static/<id=0>');

testRouteIn($route, '/presenter/action/12/any');

testRouteIn($route, '/presenter/action/12');

testRouteIn($route, '/presenter/action/static/12', 'Presenter', [
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
], '/presenter/action/static/12?test=testvalue');

testRouteIn($route, '/presenter/action/static', 'Presenter', [
	'id' => '0',
	'action' => 'action',
	'test' => 'testvalue',
], '/presenter/action/static/?test=testvalue');

testRouteIn($route, '/presenter/action/');

testRouteIn($route, '/presenter/action');

testRouteIn($route, '/presenter/', 'Presenter', [
	'id' => '0',
	'action' => 'default',
	'test' => 'testvalue',
], '/presenter/?test=testvalue');

testRouteIn($route, '/presenter', 'Presenter', [
	'id' => '0',
	'action' => 'default',
	'test' => 'testvalue',
], '/presenter/?test=testvalue');

testRouteIn($route, '/');
