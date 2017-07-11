<?php

/**
 * Test: Nette\Application\Routers\SimpleRouter invalid request.
 */

declare(strict_types=1);

use Nette\Application\Routers\SimpleRouter;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';




test(function () {
	$router = new SimpleRouter();
	$url = new Http\UrlScript('http://nette.org?presenter[]=foo');
	$httpRequest = new Http\Request($url);
	$req = $router->match($httpRequest);

	Assert::null($req);
});

test(function () {
	$router = new SimpleRouter();
	$url = new Http\UrlScript('http://nette.org');
	$httpRequest = new Http\Request($url);
	$req = $router->match($httpRequest);

	Assert::null($req);
});
