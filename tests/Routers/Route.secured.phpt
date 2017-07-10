<?php

/**
 * Test: Nette\Application\Routers\Route with Secured
 */

declare(strict_types=1);

use Nette\Application\Request;
use Nette\Application\Routers\Route;
use Nette\Http\Url;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Route.php';


$route = new Route('<param>', [
	'presenter' => 'Presenter',
]);

$url = $route->constructUrl(
	new Request('Presenter', NULL, ['param' => 'any']),
	new Url('https://example.org')
);
Assert::same('https://example.org/any', $url);
