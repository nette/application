<?php

/**
 * Test: Nette\Application\Routers\Route with optional sequence precedence.
 */

use Nette\Application\Routers\Route,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('[<one>/][<two>]', [
]);

testRouteIn($route, '/one', 'querypresenter', [
	'one' => 'one',
	'two' => NULL,
	'test' => 'testvalue',
], '/one/?test=testvalue&presenter=querypresenter');

$route = new Route('[<one>/]<two>', [
	'two' => NULL,
]);

testRouteIn($route, '/one', 'querypresenter', [
	'one' => 'one',
	'two' => NULL,
	'test' => 'testvalue',
], '/one/?test=testvalue&presenter=querypresenter');
