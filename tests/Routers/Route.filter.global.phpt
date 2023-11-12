<?php

/**
 * Test: Nette\Application\Routers\Route with FilterIn & FilterOut
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>', [
	null => [
		Route::FilterIn => function (array $arr) {
			if (substr($arr['presenter'], 0, 3) !== 'Abc') {
				return null;
			}

			$arr['presenter'] .= '.in';
			$arr['param'] .= '.in';
			return $arr;
		},
		Route::FilterOut => function (array $arr) {
			if (substr($arr['presenter'], 0, 3) !== 'Abc') {
				return null;
			}

			$arr['presenter'] .= '.out';
			$arr['param'] .= '.out';
			return $arr;
		},
	],
]);

testRouteIn($route, '/abc?param=1', [
	'presenter' => 'Abc.in',
	'param' => '1.in',
	'test' => 'testvalue',
], '/abc.in.out?param=1.in.out&test=testvalue');

testRouteIn($route, '/cde?param=1');

Assert::null(testRouteOut($route, ['presenter' => 'Cde']));


$route = new Route('<lang>/<presenter>/<action>', [
	null => [
		Route::FilterIn => function (array $arr) {
			if ($arr['presenter'] !== 'AbcCs') {
				return null;
			}

			$arr['presenter'] = substr($arr['presenter'], 0, -2); // AbcCs -> Abc
			$arr['action'] = substr($arr['action'], 0, -2);
			return $arr;
		},
		Route::FilterOut => function (array $arr) {
			if ($arr['presenter'] !== 'Abc') {
				return null;
			}

			$arr['presenter'] .= ucfirst($arr['lang']); // Abc -> AbcCs
			$arr['action'] .= ucfirst($arr['lang']);
			return $arr;
		},
	],
]);


testRouteIn($route, '/cs/abc-cs/def-cs', [
	'presenter' => 'Abc',
	'lang' => 'cs',
	'action' => 'def',
	'test' => 'testvalue',
], '/cs/abc-cs/def-cs?test=testvalue');

Assert::same(
	'http://example.com/cs/abc-cs/def-cs?test=testvalue',
	testRouteOut($route, ['presenter' => 'Abc', 'lang' => 'cs', 'action' => 'def', 'test' => 'testvalue']),
);
