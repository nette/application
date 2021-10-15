<?php

/**
 * Test: Nette\Application\Routers\RouteList default usage.
 */

declare(strict_types=1);

use Nette\Application\Routers\RouteList;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$list = new RouteList;
$list->addRoute('<presenter>/<action=default>/<id= \d{1,3}>');


Assert::same('http://example.com/front.homepage/', testRouteOut($list, ['presenter' => 'Front:Homepage']));

testRouteIn($list, '/presenter/action/12/any');

testRouteIn($list, '/presenter/action/12/', [
	'presenter' => 'Presenter',
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
], '/presenter/action/12?test=testvalue');

testRouteIn($list, '/presenter/action/12/any');

testRouteIn($list, '/presenter/action/12/', [
	'presenter' => 'Presenter',
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
], '/presenter/action/12?test=testvalue');

testRouteIn($list, '/');
