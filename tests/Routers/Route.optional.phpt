<?php

/**
 * Test: Nette\Application\Routers\Route with optional sequence.
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('index[.html]', [
]);

testRouteIn($route, '/index.html', 'querypresenter', [
	'test' => 'testvalue',
], '/index?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/index', 'querypresenter', [
	'test' => 'testvalue',
], '/index?test=testvalue&presenter=querypresenter');
