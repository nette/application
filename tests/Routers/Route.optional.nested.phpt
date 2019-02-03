<?php

/**
 * Test: Nette\Application\Routers\Route with nested optional sequences.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('[<lang [a-z]{2}>[-<sub>]/]<name>[/page-<page>]', [
	'sub' => 'cz',
]);

testRouteIn($route, '/cs-cz/name', [
	'presenter' => 'querypresenter',
	'lang' => 'cs',
	'sub' => 'cz',
	'name' => 'name',
	'page' => null,
	'test' => 'testvalue',
], '/cs/name?presenter=querypresenter&test=testvalue');

testRouteIn($route, '/cs-xx/name', [
	'presenter' => 'querypresenter',
	'lang' => 'cs',
	'sub' => 'xx',
	'name' => 'name',
	'page' => null,
	'test' => 'testvalue',
], '/cs-xx/name?presenter=querypresenter&test=testvalue');

testRouteIn($route, '/cs/name', [
	'presenter' => 'querypresenter',
	'lang' => 'cs',
	'name' => 'name',
	'sub' => 'cz',
	'page' => null,
	'test' => 'testvalue',
], '/cs/name?presenter=querypresenter&test=testvalue');

testRouteIn($route, '/name', [
	'presenter' => 'querypresenter',
	'name' => 'name',
	'sub' => 'cz',
	'page' => null,
	'lang' => null,
	'test' => 'testvalue',
], '/name?presenter=querypresenter&test=testvalue');

testRouteIn($route, '/name/page-0', [
	'presenter' => 'querypresenter',
	'name' => 'name',
	'page' => '0',
	'sub' => 'cz',
	'lang' => null,
	'test' => 'testvalue',
], '/name/page-0?presenter=querypresenter&test=testvalue');

testRouteIn($route, '/name/page-');

testRouteIn($route, '/');
