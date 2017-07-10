<?php

/**
 * Test: Nette\Application\Routers\Route with nested optional sequences.
 */

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('[<lang [a-z]{2}>[-<sub>]/]<name>[/page-<page>]', [
	'sub' => 'cz',
]);

testRouteIn($route, '/cs-cz/name', 'querypresenter', [
	'lang' => 'cs',
	'sub' => 'cz',
	'name' => 'name',
	'page' => null,
	'test' => 'testvalue',
], '/cs/name?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/cs-xx/name', 'querypresenter', [
	'lang' => 'cs',
	'sub' => 'xx',
	'name' => 'name',
	'page' => null,
	'test' => 'testvalue',
], '/cs-xx/name?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/cs/name', 'querypresenter', [
	'lang' => 'cs',
	'name' => 'name',
	'sub' => 'cz',
	'page' => null,
	'test' => 'testvalue',
], '/cs/name?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/name', 'querypresenter', [
	'name' => 'name',
	'sub' => 'cz',
	'page' => null,
	'lang' => null,
	'test' => 'testvalue',
], '/name?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/name/page-0', 'querypresenter', [
	'name' => 'name',
	'page' => '0',
	'sub' => 'cz',
	'lang' => null,
	'test' => 'testvalue',
], '/name/page-0?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/name/page-');

testRouteIn($route, '/');
