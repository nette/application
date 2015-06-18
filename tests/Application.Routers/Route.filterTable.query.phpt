<?php

/**
 * Test: Nette\Application\Routers\Route with FilterTable
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route(' ? action=<presenter>', [
	'presenter' => [
		Route::FILTER_TABLE => [
			'produkt' => 'Product',
			'kategorie' => 'Category',
			'zakaznik' => 'Customer',
			'kosik' => 'Basket',
		],
	],
]);

testRouteIn($route, '/?action=kategorie', 'Category', [
	'test' => 'testvalue',
], '/?test=testvalue&action=kategorie');
