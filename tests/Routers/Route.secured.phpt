<?php

/**
 * Test: Nette\Application\Routers\Route with Secured
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Nette\Http\UrlScript;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<param>', [
	'presenter' => 'Presenter',
]);

$url = $route->constructUrl(
	['presenter' => 'Presenter', 'param' => 'any'],
	new UrlScript('https://example.org'),
);
Assert::same('https://example.org/any', $url);
