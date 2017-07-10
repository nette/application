<?php

/**
 * Test: Nette\Application\Routers\Route with FILTER_IN & FILTER_OUT using string <=> object conversion
 */

use Nette\Application\Routers\Route;
use Tester\Assert;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$identityMap = [];
$identityMap[1] = new RouterObject(1);
$identityMap[2] = new RouterObject(2);


$route = new Route('<parameter>', [
	'presenter' => 'presenter',
	'parameter' => [
		Route::FILTER_IN => function ($s) use ($identityMap) {
			return isset($identityMap[$s]) ? $identityMap[$s] : null;
		},
		Route::FILTER_OUT => function ($obj) {
			return $obj instanceof RouterObject ? $obj->getId() : null;
		},
	],
]);


// Match
testRouteIn($route, '/1/', 'presenter', [
	'parameter' => $identityMap[1],
	'test' => 'testvalue',
], '/1?test=testvalue');

Assert::same('http://example.com/1', testRouteOut($route, 'presenter', [
	'parameter' => $identityMap[1],
]));


// Doesn't match
testRouteIn($route, '/3/');

Assert::null(testRouteOut($route, 'presenter', [
	'parameter' => null,
]));


class RouterObject
{
	/** @var int */
	private $id;


	public function __construct($id)
	{
		$this->id = $id;
	}


	public function getId()
	{
		return $this->id;
	}
}
