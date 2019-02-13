<?php

/**
 * Test: Nette\Application\Routers\Route with optional sequence and two parameters.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('[<one [a-z]+><two [0-9]+>]', [
	'one' => 'a',
	'two' => '1',
]);

testRouteIn($route, '/a1', [
	'presenter' => 'querypresenter',
	'one' => 'a',
	'two' => '1',
	'test' => 'testvalue',
], '/?presenter=querypresenter&test=testvalue');

testRouteIn($route, '/x1', [
	'presenter' => 'querypresenter',
	'one' => 'x',
	'two' => '1',
	'test' => 'testvalue',
], '/x1?presenter=querypresenter&test=testvalue');

testRouteIn($route, '/a2', [
	'presenter' => 'querypresenter',
	'one' => 'a',
	'two' => '2',
	'test' => 'testvalue',
], '/a2?presenter=querypresenter&test=testvalue');

testRouteIn($route, '/x2', [
	'presenter' => 'querypresenter',
	'one' => 'x',
	'two' => '2',
	'test' => 'testvalue',
], '/x2?presenter=querypresenter&test=testvalue');
