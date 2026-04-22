<?php declare(strict_types=1);

use Nette\Application\PresenterFactory;
use Nette\Application\Routers\RouteList;
use Nette\Bridges\ApplicationTracy\RoutingPanel;
use Nette\Http;
use Nette\Http\UrlScript;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$router = new RouteList;
$router->addRoute('sign-in', 'Sign:in');
$router->withPath('admin/')
	->addRoute('', 'Admin:Home:default')
	->addRoute('<presenter>/<action=default>', ['module' => 'Admin']);
$router->addRoute('<presenter>/<action=default>');


test('matched route', function () use ($router) {
	$httpRequest = new Http\Request(new UrlScript('http://example.com/admin', '/'));
	$panel = new RoutingPanel($router, $httpRequest, new PresenterFactory(function () {}));
	$panel->getTab();

	Assert::match(
		<<<'XX'
			%A%
			## Routing

			`GET http://example.com/admin`

			**Matched:** Admin:Home:default
			%A%

			### Routes
			- [no match] `sign-in` (presenter=Sign, action=in)
			- [MATCHED] `admin/` (presenter=Admin:Home, action=default)
			- [no match] `admin/<presenter>/<action=default>` (module=Admin, action=default)
			- [MAY MATCH] `<presenter>/<action=default>` (action=default)
			XX,
		$panel->getAgentInfo(),
	);
});


test('no matching route', function () {
	$router = new RouteList;
	$router->addRoute('sign-in', 'Sign:in');

	$httpRequest = new Http\Request(new UrlScript('http://example.com/nowhere', '/'));
	$panel = new RoutingPanel($router, $httpRequest, new PresenterFactory(function () {}));
	$panel->getTab();

	Assert::match(
		<<<'XX'
			%A%
			## Routing

			`GET http://example.com/nowhere`

			**Matched:** no route

			### Routes
			- [no match] `sign-in` (presenter=Sign, action=in)
			XX,
		$panel->getAgentInfo(),
	);
});


test('no routes defined', function () {
	$router = new RouteList;

	$httpRequest = new Http\Request(new UrlScript('http://example.com/', '/'));
	$panel = new RoutingPanel($router, $httpRequest, new PresenterFactory(function () {}));
	$panel->getTab();

	Assert::match(
		<<<'XX'
			%A%
			## Routing

			`GET http://example.com/`

			**Matched:** no route

			### Routes
			(none defined)
			XX,
		$panel->getAgentInfo(),
	);
});


test('matched with extra parameters', function () use ($router) {
	$httpRequest = new Http\Request(new UrlScript('http://example.com/article/detail?id=42', '/'));
	$panel = new RoutingPanel($router, $httpRequest, new PresenterFactory(function () {}));
	$panel->getTab();

	$output = $panel->getAgentInfo();
	Assert::contains('**Matched:** Article:detail', $output);
	Assert::contains('**Parameters:**', $output);
	Assert::contains('- id = 42', $output);
});
