<?php

/**
 * Test: Nette\Application\Routers\SimpleRouter and modules.
 */

declare(strict_types=1);

use Nette\Application;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$router = @new Application\Routers\SimpleRouter([ // @ is deprecated
	'module' => 'main:sub',
]);

$url = new Http\Url('http://nette.org/file.php', '/file.php');
$url->setQuery([
	'presenter' => 'myPresenter',
]);
$httpRequest = new Http\Request(new Http\UrlScript($url));

$req = $router->match($httpRequest);
Assert::same('main:sub:myPresenter', $req['presenter']);

$url = $router->constructUrl($req, $httpRequest->getUrl());
Assert::same('http://nette.org/file.php?presenter=myPresenter', $url);

$url = $router->constructUrl(['presenter' => 'othermodule:presenter'], $httpRequest->getUrl());
Assert::null($url);
