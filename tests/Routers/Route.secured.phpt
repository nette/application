<?php

/**
 * Test: Nette\Application\Routers\Route with Secured
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<param>', [
	'presenter' => 'Presenter',
], Route::SECURED);

testRouteIn($route, '/any', 'Presenter', [
	'param' => 'any',
	'test' => 'testvalue',
], 'https://example.com/any?test=testvalue');
