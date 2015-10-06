<?php

/**
 * Test: Nette\Application\Routers\Route with UrlEncoding
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<param .*>', [
	'presenter' => 'Presenter',
]);

testRouteIn($route, '/a%3A%25%2Fb', 'Presenter', [
	'param' => 'a:%/b',
	'test' => 'testvalue',
], '/a%3A%25/b?test=testvalue');


$route = new Route('<param .*>', [
	'presenter' => 'Presenter',
	'param' => [
		Route::FILTER_OUT => 'rawurlencode',
	],
]);

testRouteIn($route, '/a%3A%25%2Fb', 'Presenter', [
	'param' => 'a:%/b',
	'test' => 'testvalue',
], '/a%3A%25%2Fb?test=testvalue');
