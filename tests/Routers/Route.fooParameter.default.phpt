<?php declare(strict_types=1);

/**
 * Test: Nette\Application\Routers\Route with FooParameter
 */

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('index<?.xml \.html?|\.php|>/', [
	'presenter' => 'DefaultPresenter',
]);

testRouteIn($route, '/index.');

testRouteIn($route, '/index.xml', [
	'presenter' => 'DefaultPresenter',
	'test' => 'testvalue',
], '/index.xml/?test=testvalue');

testRouteIn($route, '/index.php', [
	'presenter' => 'DefaultPresenter',
	'test' => 'testvalue',
], '/index.xml/?test=testvalue');

testRouteIn($route, '/index.htm', [
	'presenter' => 'DefaultPresenter',
	'test' => 'testvalue',
], '/index.xml/?test=testvalue');

testRouteIn($route, '/index', [
	'presenter' => 'DefaultPresenter',
	'test' => 'testvalue',
], '/index.xml/?test=testvalue');
