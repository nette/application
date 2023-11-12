<?php

/**
 * Test: Nette\Application\Routers\Route with FilterTable
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route(' ? action=<presenter>', [
	'presenter' => [
		Route::FilterTable => [
			'produkt' => 'Product',
			'kategorie' => 'Category',
			'zakaznik' => 'Customer',
			'kosik' => 'Basket',
		],
	],
]);

testRouteIn($route, '/?action=kategorie', [
	'presenter' => 'Category',
	'test' => 'testvalue',
], '/?action=kategorie&test=testvalue');
