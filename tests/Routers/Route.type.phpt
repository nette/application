<?php

/**
 * Test: Nette\Application\Routers\Route default usage.
 */

declare(strict_types=1);

use Nette\Application\Request;
use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<id=5>');
$request = new Request('p', NULL, ['id' => 5]);

Assert::same(
	'http://example.com/?presenter=p',
	$route->constructUrl($request, new Nette\Http\UrlScript('http://example.com'))
);
