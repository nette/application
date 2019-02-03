<?php

/**
 * Test: Nette\Application\Routers\CliRouter basic usage
 */

declare(strict_types=1);

use Nette\Application\Routers\CliRouter;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


// php.exe app.phpc homepage:default name --verbose -user "john doe" "-pass=se cret" /wait
$_SERVER['argv'] = [
	'app.phpc',
	'homepage:default',
	'name',
	'--verbose',
	'-user',
	'john doe',
	'-pass=se cret',
	'/wait',
];

$httpRequest = new Http\Request(new Http\UrlScript);

$router = new CliRouter([
	'id' => 12,
	'user' => 'anyvalue',
]);
$params = $router->match($httpRequest);

Assert::same([
	'id' => 12,
	'user' => 'john doe',
	'action' => 'default',
	0 => 'name',
	'verbose' => true,
	'pass' => 'se cret',
	'wait' => true,
	'presenter' => 'homepage',
], $params);


Assert::null($router->constructUrl($params, $httpRequest->getUrl()));
