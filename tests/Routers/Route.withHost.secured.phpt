<?php

/**
 * Test: Nette\Application\Routers\Route with WithHost
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Nette\Http\UrlScript;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('//example.org/test', [
	'presenter' => 'Default',
	'action' => 'default',
]);

$url = $route->constructUrl(
	['presenter' => 'Default', 'action' => 'default'],
	new UrlScript('https://example.org'),
);
Assert::same('https://example.org/test', $url);

$url = $route->constructUrl(
	['presenter' => 'Default', 'action' => 'default'],
	new UrlScript('https://example.com'),
);
Assert::same('https://example.org/test', $url);



$route = new Route('https://example.org/test', [
	'presenter' => 'Default',
	'action' => 'default',
]);

$url = $route->constructUrl(
	['presenter' => 'Default', 'action' => 'default'],
	new UrlScript('https://example.org'),
);
Assert::same('https://example.org/test', $url);

$url = $route->constructUrl(
	['presenter' => 'Default', 'action' => 'default'],
	new UrlScript('https://example.com'),
);
Assert::same('https://example.org/test', $url);
