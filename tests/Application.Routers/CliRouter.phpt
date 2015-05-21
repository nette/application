<?php

/**
 * Test: Nette\Application\Routers\CliRouter basic usage
 */

use Nette\Http,
	Nette\Application\Routers\CliRouter,
	Tester\Assert;


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

$httpRequest = new Http\Request(new Http\UrlScript());

$router = new CliRouter([
	'id' => 12,
	'user' => 'anyvalue',
]);
$req = $router->match($httpRequest);

Assert::same( 'homepage', $req->getPresenterName() );

Assert::same( [
	'id' => 12,
	'user' => 'john doe',
	'action' => 'default',
	0 => 'name',
	'verbose' => TRUE,
	'pass' => 'se cret',
	'wait' => TRUE,
], $req->getParameters() );

Assert::true( $req->isMethod('cli') );


Assert::null( $router->constructUrl($req, $httpRequest->getUrl()) );
