<?php

/**
 * Test: Nette\Application\Routers\Route default usage.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>/<action=default>/<id= \d{1,3}>');

Assert::same('http://example.com/homepage/', testRouteOut($route, ['presenter' => 'Homepage']));

Assert::same('http://example.com/homepage/', testRouteOut($route, ['presenter' => 'Homepage', 'action' => 'default']));

Assert::null(testRouteOut($route, ['presenter' => 'Homepage', 'id' => 'word']));

Assert::same('http://example.com/front.homepage/', testRouteOut($route, ['presenter' => 'Front:Homepage']));

testRouteIn($route, '/presenter/action/12/any');

testRouteIn($route, '/presenter/action/12/', [
	'presenter' => 'Presenter',
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
], '/presenter/action/12?test=testvalue');

testRouteIn($route, '/presenter/action/12', [
	'presenter' => 'Presenter',
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
], '/presenter/action/12?test=testvalue');

testRouteIn($route, '/presenter/action/1234');

testRouteIn($route, '/presenter/action/', [
	'presenter' => 'Presenter',
	'action' => 'action',
	'id' => '',
	'test' => 'testvalue',
], '/presenter/action/?test=testvalue');

testRouteIn($route, '/presenter/action', [
	'presenter' => 'Presenter',
	'action' => 'action',
	'id' => '',
	'test' => 'testvalue',
], '/presenter/action/?test=testvalue');

testRouteIn($route, '/presenter/', [
	'presenter' => 'Presenter',
	'id' => '',
	'action' => 'default',
	'test' => 'testvalue',
], '/presenter/?test=testvalue');

testRouteIn($route, '/presenter', [
	'presenter' => 'Presenter',
	'id' => '',
	'action' => 'default',
	'test' => 'testvalue',
], '/presenter/?test=testvalue');

testRouteIn($route, '/');
