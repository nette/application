<?php

/**
 * Test: Nette\Application\Routers\Route with WithParamsInQuery
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<action> ? <presenter>', [
	'presenter' => 'Default',
	'action' => 'default',
]);

testRouteIn($route, '/action/', 'querypresenter', [
	'action' => 'action',
	'test' => 'testvalue',
], '/action?test=testvalue&presenter=querypresenter');

testRouteIn($route, '/', 'querypresenter', [
	'action' => 'default',
	'test' => 'testvalue',
], '/?test=testvalue&presenter=querypresenter');
