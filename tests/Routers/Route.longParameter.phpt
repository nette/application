<?php

/**
 * Test: Nette\Application\Routers\Route with LongParameter
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<parameter-longer-than-32-characters>', array(
	'presenter' => 'Presenter',
));

testRouteIn($route, '/any', 'Presenter', array(
	'parameter-longer-than-32-characters' => 'any',
	'test' => 'testvalue',
), '/any?test=testvalue');
