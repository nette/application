<?php

/**
 * Test: Nette\Application\Routers\Route default usage.
 */

use Nette\Application\Routers\Route,
	Nette\Application\Request,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.inc';


$route = new Route('<id=5>');
$request = new Request('p', NULL, ['id' => 5]);

Assert::same(
	'http://example.com/?presenter=p',
	$route->constructUrl($request, new Nette\Http\UrlScript('http://example.com'))
);
