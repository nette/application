<?php

/**
 * Test: Nette\Application\Routers\Route with scalar params
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


test('', function () {
	$route = new Route('<presenter>/<param>', [
	]);

	Assert::same(
		'http://example.com/homepage/12',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => 12])
	);

	Assert::same(
		'http://example.com/homepage/12.1',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => 12.1])
	);

	Assert::same(
		'http://example.com/homepage/0',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => false])
	);

	Assert::same(
		'http://example.com/homepage/1',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => true])
	);

	Assert::null(testRouteOut($route, ['presenter' => 'Homepage', 'param' => null]));
	Assert::null(testRouteOut($route, ['presenter' => 'Homepage', 'param' => '']));
});


test('', function () {
	$route = new Route('<presenter>/<param>', [
		'param' => 12,
	]);

	Assert::same(
		'http://example.com/homepage/',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => 12])
	);

	Assert::same(
		'http://example.com/homepage/12.1',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => 12.1])
	);

	Assert::same(
		'http://example.com/homepage/0',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => false])
	);

	Assert::same(
		'http://example.com/homepage/1',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => true])
	);

	Assert::same(
		'http://example.com/homepage/',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => null])
	);

	Assert::null(testRouteOut($route, ['presenter' => 'Homepage', 'param' => '']));
});


test('', function () {
	$route = new Route('<presenter>/<param>', [
		'param' => 12.1,
	]);

	Assert::same(
		'http://example.com/homepage/12',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => 12])
	);

	Assert::same(
		'http://example.com/homepage/',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => 12.1])
	);

	Assert::same(
		'http://example.com/homepage/0',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => false])
	);

	Assert::same(
		'http://example.com/homepage/1',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => true])
	);

	Assert::same(
		'http://example.com/homepage/',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => null])
	);

	Assert::null(testRouteOut($route, ['presenter' => 'Homepage', 'param' => '']));
});


test('', function () {
	$route = new Route('<presenter>/<param>', [
		'param' => true,
	]);

	Assert::same(
		'http://example.com/homepage/12',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => 12])
	);

	Assert::same(
		'http://example.com/homepage/12.1',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => 12.1])
	);

	Assert::same(
		'http://example.com/homepage/0',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => false])
	);

	Assert::same(
		'http://example.com/homepage/',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => true])
	);

	Assert::same(
		'http://example.com/homepage/',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => null])
	);

	Assert::null(testRouteOut($route, ['presenter' => 'Homepage', 'param' => '']));
});


test('', function () {
	$route = new Route('<presenter>/<param>', [
		'param' => false,
	]);

	Assert::same(
		'http://example.com/homepage/12',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => 12])
	);

	Assert::same(
		'http://example.com/homepage/12.1',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => 12.1])
	);

	Assert::same(
		'http://example.com/homepage/',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => false])
	);

	Assert::same(
		'http://example.com/homepage/1',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => true])
	);

	Assert::same(
		'http://example.com/homepage/',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => null])
	);

	Assert::null(testRouteOut($route, ['presenter' => 'Homepage', 'param' => '']));
});


test('', function () {
	$route = new Route('<presenter>/<param>', [
		'param' => null,
	]);

	Assert::same(
		'http://example.com/homepage/12',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => 12])
	);

	Assert::same(
		'http://example.com/homepage/12.1',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => 12.1])
	);

	Assert::same(
		'http://example.com/homepage/0',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => false])
	);

	Assert::same(
		'http://example.com/homepage/1',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => true])
	);

	Assert::same(
		'http://example.com/homepage/',
		testRouteOut($route, ['presenter' => 'Homepage', 'param' => null])
	);
});
