<?php

/**
 * Test: Nette\Application\Routers\Route with FILTER_IN & FILTER_OUT
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>', [
	NULL => [
		Route::FILTER_IN => function (array $arr) {
			if (substr($arr['presenter'], 0, 3) !== 'Abc') {
				return NULL;
			}
			$arr['presenter'] .= '.in';
			$arr['param'] .= '.in';
			return $arr;
		},
		Route::FILTER_OUT => function (array $arr) {
			if (substr($arr['presenter'], 0, 3) !== 'Abc') {
				return NULL;
			}
			$arr['presenter'] .= '.out';
			$arr['param'] .= '.out';
			return $arr;
		},
	],
]);

testRouteIn($route, '/abc?param=1', 'Abc.in', [
	'param' => '1.in',
	'test' => 'testvalue',
], '/abc.in.out?param=1.in.out&test=testvalue');

testRouteIn($route, '/cde?param=1');

Assert::null(testRouteOut($route, 'Cde'));


$route = new Route('<lang>/<presenter>/<action>', [
	NULL => [
		Route::FILTER_IN => function (array $arr) {
			if ($arr['module'] !== 'App') {
				return NULL;
			}
			if ($arr['presenter'] !== 'AbcCs') {
				return NULL;
			}
			$arr['presenter'] =  substr($arr['presenter'], 0, -2); // AbcCs -> Abc
			$arr['action'] = substr($arr['action'], 0, -2);
			return $arr;
		},
		Route::FILTER_OUT => function (array $arr) {
			if ($arr['module'] !== 'App') {
				return NULL;
			}
			if ($arr['presenter'] !== 'Abc') {
				return NULL;
			}
			$arr['presenter'] .= ucfirst($arr['lang']); // Abc -> AbcCs
			$arr['action'] .= ucfirst($arr['lang']);
			return $arr;
		},
	],
	'module' => 'App'
]);


testRouteIn($route, '/cs/abc-cs/def-cs', 'App:Abc', [
	'lang' => 'cs',
	'action' => 'def',
	'test' => 'testvalue',
], '/cs/abc-cs/def-cs?test=testvalue');

Assert::same(
	'http://example.com/cs/abc-cs/def-cs?test=testvalue',
	testRouteOut($route, 'App:Abc', ['lang' => 'cs', 'action' => 'def', 'test' => 'testvalue'])
);
