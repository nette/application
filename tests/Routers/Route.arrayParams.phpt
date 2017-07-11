<?php

/**
 * Test: Nette\Application\Routers\Route with ArrayParams
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route(' ? arr=<arr>', [
	'presenter' => 'Default',
	'arr' => '',
]);

testRouteIn($route, '/?arr[1]=1&arr[2]=2', 'Default', [
	'arr' => [
		1 => '1',
		2 => '2',
	],
	'test' => 'testvalue',
], '/?test=testvalue&arr%5B1%5D=1&arr%5B2%5D=2');
