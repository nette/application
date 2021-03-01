<?php

/**
 * Test: Nette\Application\Routers\Route default usage.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<id=5>');
$params = ['id' => 5, 'presenter' => 'p'];

Assert::same(
	'http://example.com/?presenter=p',
	$route->constructUrl($params, new Nette\Http\UrlScript('http://example.com')),
);
