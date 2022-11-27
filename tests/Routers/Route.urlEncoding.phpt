<?php

/**
 * Test: Nette\Application\Routers\Route with UrlEncoding
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<param .*>', [
	'presenter' => 'Presenter',
]);

testRouteIn($route, '/a%3A%25%2Fb', [
	'presenter' => 'Presenter',
	'param' => 'a:%/b',
	'test' => 'testvalue',
], '/a%3A%25/b?test=testvalue');


$route = new Route('<param .*>', [
	'presenter' => 'Presenter',
	'param' => [
		Route::FilterOut => 'rawurlencode',
	],
]);

testRouteIn($route, '/a%3A%25%2Fb', [
	'presenter' => 'Presenter',
	'param' => 'a:%/b',
	'test' => 'testvalue',
], '/a%3A%25%2Fb?test=testvalue');
