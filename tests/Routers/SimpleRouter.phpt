<?php

/**
 * Test: Nette\Application\Routers\SimpleRouter basic functions.
 */

declare(strict_types=1);

use Nette\Application\Routers\SimpleRouter;
use Nette\Http;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$router = new SimpleRouter([
	'id' => 12,
	'any' => 'anyvalue',
]);

$url = new Http\Url('http://nette.org/file.php');
$url->setQuery([
	'presenter' => 'myPresenter',
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
]);
$httpRequest = new Http\Request(new Http\UrlScript($url, '/file.php'));

$params = $router->match($httpRequest);
Assert::same([
	'presenter' => 'myPresenter',
	'action' => 'action',
	'id' => '12',
	'test' => 'testvalue',
	'any' => 'anyvalue',
], $params);

$res = $router->constructUrl($params, $httpRequest->getUrl());
Assert::same('http://nette.org/file.php?presenter=myPresenter&action=action&test=testvalue', $res);


$url = new Http\UrlScript('https://nette.org/file.php');
$res = $router->constructUrl($params, $url);
Assert::same('https://nette.org/file.php?presenter=myPresenter&action=action&test=testvalue', $res);
