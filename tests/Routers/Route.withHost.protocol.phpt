<?php

/**
 * Test: Nette\Application\Routers\Route with host & protocol
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('http://<host>.<domain>/<path>', [
	'presenter' => 'Default',
	'action' => 'default',
]);

testRouteIn($route, '/abc', [
	'presenter' => 'Default',
	'host' => 'example',
	'domain' => 'com',
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], '/abc?test=testvalue');


$route = new Route('https://<host>.<domain>/<path>', [
	'presenter' => 'Default',
	'action' => 'default',
]);

testRouteIn($route, '/abc', [
	'presenter' => 'Default',
	'host' => 'example',
	'domain' => 'com',
	'path' => 'abc',
	'action' => 'default',
	'test' => 'testvalue',
], 'https://example.com/abc?test=testvalue');
