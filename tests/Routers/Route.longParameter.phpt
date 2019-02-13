<?php

/**
 * Test: Nette\Application\Routers\Route with LongParameter
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<parameter-longer-than-32-characters>', [
	'presenter' => 'Presenter',
]);

testRouteIn($route, '/any', [
	'presenter' => 'Presenter',
	'parameter-longer-than-32-characters' => 'any',
	'test' => 'testvalue',
], '/any?test=testvalue');
