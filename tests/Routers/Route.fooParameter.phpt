<?php

/**
 * Test: Nette\Application\Routers\Route with FooParameter
 */

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('index<?.xml>/', [
	'presenter' => 'DefaultPresenter',
]);


testRouteIn($route, '/index.');

testRouteIn($route, '/index.xml', 'DefaultPresenter', [
	'test' => 'testvalue',
], '/index.xml/?test=testvalue');

testRouteIn($route, '/index.php');

testRouteIn($route, '/index');
