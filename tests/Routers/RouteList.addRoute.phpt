<?php

declare(strict_types=1);

use Nette\Application\Routers\RouteList;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$list = new RouteList;
$list->addRoute('foo', ['presenter' => 'foo'], RouteList::ONE_WAY);
$list->addRoute('bar', ['presenter' => 'bar'], oneWay: true);
$list->addRoute('hello', ['presenter' => 'hello']);


testRouteIn($list, '/foo', ['presenter' => 'foo', 'test' => 'testvalue']);

testRouteIn($list, '/bar', ['presenter' => 'bar', 'test' => 'testvalue']);

testRouteIn(
	$list,
	'/hello',
	['presenter' => 'hello', 'test' => 'testvalue'],
	'/hello?test=testvalue',
);

testRouteIn($list, '/none');
