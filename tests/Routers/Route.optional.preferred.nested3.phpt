<?php

/**
 * Test: Nette\Application\Routers\Route with 'required' optional sequences III.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('[!<lang [a-z]{2}>[-<sub>]/]<name>[/page-<page>]', [
	'sub' => 'cz',
	'lang' => 'cs',
]);

testRouteIn($route, '/cs-cz/name', 'querypresenter', [
	'lang' => 'cs',
	'sub' => 'cz',
	'name' => 'name',
	'page' => NULL,
	'test' => 'testvalue',
], '/cs/name?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/cs-xx/name', 'querypresenter', [
	'lang' => 'cs',
	'sub' => 'xx',
	'name' => 'name',
	'page' => NULL,
	'test' => 'testvalue',
], '/cs-xx/name?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/name', 'querypresenter', [
	'name' => 'name',
	'sub' => 'cz',
	'lang' => 'cs',
	'page' => NULL,
	'test' => 'testvalue',
], '/cs/name?test=testvalue&presenter=querypresenter');
