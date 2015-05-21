<?php

/**
 * Test: Nette\Application\Routers\SimpleRouter with secured connection.
 */

use Nette\Http,
	Nette\Application,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$router = new Application\Routers\SimpleRouter([
	'id' => 12,
	'any' => 'anyvalue',
], Application\Routers\SimpleRouter::SECURED);

$url = new Http\UrlScript('http://nette.org/file.php');
$url->setScriptPath('/file.php');
$url->setQuery([
	'presenter' => 'myPresenter',
]);
$httpRequest = new Http\Request($url);

$req = new Application\Request(
	'othermodule:presenter',
	Http\Request::GET,
	[]
);

$url = $router->constructUrl($req, $httpRequest->getUrl());
Assert::same( 'https://nette.org/file.php?presenter=othermodule%3Apresenter',  $url );
