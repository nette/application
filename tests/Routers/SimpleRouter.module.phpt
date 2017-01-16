<?php

/**
 * Test: Nette\Application\Routers\SimpleRouter and modules.
 */

declare(strict_types=1);

use Nette\Http;
use Nette\Application;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$router = new Application\Routers\SimpleRouter([
	'module' => 'main:sub',
]);

$url = new Http\UrlScript('http://nette.org/file.php');
$url->setScriptPath('/file.php');
$url->setQuery([
	'presenter' => 'myPresenter',
]);
$httpRequest = new Http\Request($url);

$req = $router->match($httpRequest);
Assert::same('main:sub:myPresenter',  $req->getPresenterName());

$url = $router->constructUrl($req, $httpRequest->getUrl());
Assert::same('http://nette.org/file.php?presenter=myPresenter',  $url);

$req = new Application\Request(
	'othermodule:presenter',
	Http\Request::GET,
	[]
);
$url = $router->constructUrl($req, $httpRequest->getUrl());
Assert::null($url);
