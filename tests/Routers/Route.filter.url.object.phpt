<?php

/**
 * Test: Nette\Application\Routers\Route with FilterIn & FilterOut using string <=> object conversion
 */

declare(strict_types=1);

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
		Route::FilterIn => fn($s) => $identityMap[$s] ?? null,
		Route::FilterOut => fn($obj) => $obj instanceof RouterObject ? $obj->getId() : null,
	],
]);


// Match
testRouteIn($route, '/1/', [
	'presenter' => 'presenter',
	'parameter' => $identityMap[1],
	'test' => 'testvalue',
], '/1?test=testvalue');

Assert::same('http://example.com/1', testRouteOut($route, [
	'presenter' => 'presenter',
	'parameter' => $identityMap[1],
]));


// Doesn't match
testRouteIn($route, '/3/');

Assert::null(testRouteOut($route, [
	'presenter' => 'presenter',
	'parameter' => null,
]));


class RouterObject
{
	private int $id;


	public function __construct($id)
	{
		$this->id = $id;
	}


	public function getId()
	{
		return $this->id;
	}
}
