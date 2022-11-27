<?php

/**
 * Test: Nette\Application\Routers\Route with FilterTable
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<presenter>', [
	'presenter' => [
		Route::FilterTable => [
			'produkt' => 'Product',
			'kategorie' => 'Category',
			'zakaznik' => 'Customer',
			'kosik' => 'Basket',
		],
		Route::FilterStrict => true,
	],
]);

testRouteIn($route, '/kategorie/', [
	'presenter' => 'Category',
	'test' => 'testvalue',
], '/kategorie?test=testvalue');

testRouteIn($route, '/other/');
