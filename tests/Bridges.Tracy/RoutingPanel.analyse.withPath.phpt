<?php

declare(strict_types=1);

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
$router->addRoute('', 'Article:homepage');
$router->addRoute('<presenter>/<action=default>');


test('URL: /', function () use ($router) {
	$httpRequest = new Http\Request(new UrlScript('/', '/'));
	$panel = new RoutingPanel($router, $httpRequest, new PresenterFactory(function () {}));

	$res = Assert::with($panel, fn() => $this->analyse($router, $httpRequest));

	Assert::equal([
		'path' => null,
		'domain' => null,
		'module' => null,
		'routes' => [
			(object) [
				'matched' => 'no',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['presenter' => 'Sign', 'action' => 'in'],
				'mask' => 'sign-in',
				'params' => null,
				'error' => null,
			],
			[
				'path' => 'admin/',
				'domain' => null,
				'module' => null,
				'routes' => [
					(object) [
						'matched' => 'no',
						'class' => 'Nette\Application\Routers\Route',
						'defaults' => ['presenter' => 'Admin:Home', 'action' => 'default'],
						'mask' => '',
						'params' => null,
						'error' => null,
					],
					(object) [
						'matched' => 'no',
						'class' => 'Nette\Application\Routers\Route',
						'defaults' => ['module' => 'Admin', 'action' => 'default'],
						'mask' => '<presenter>/<action=default>',
						'params' => null,
						'error' => null,
					],
				],
			],
			(object) [
				'matched' => 'yes',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['presenter' => 'Article', 'action' => 'homepage'],
				'mask' => '',
				'params' => ['presenter' => 'Article', 'action' => 'homepage'],
				'error' => null,
			],
			(object) [
				'matched' => 'no',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['action' => 'default'],
				'mask' => '<presenter>/<action=default>',
				'params' => null,
				'error' => null,
			],
		],
	], $res);
});


test('URL: /foo', function () use ($router) {
	$httpRequest = new Http\Request(new UrlScript('/foo', '/'));
	$panel = new RoutingPanel($router, $httpRequest, new PresenterFactory(function () {}));

	$res = Assert::with($panel, fn() => $this->analyse($router, $httpRequest));

	Assert::equal([
		'path' => null,
		'domain' => null,
		'module' => null,
		'routes' => [
			(object) [
				'matched' => 'no',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['presenter' => 'Sign', 'action' => 'in'],
				'mask' => 'sign-in',
				'params' => null,
				'error' => null,
			],
			[
				'path' => 'admin/',
				'domain' => null,
				'module' => null,
				'routes' => [
					(object) [
						'matched' => 'no',
						'class' => 'Nette\Application\Routers\Route',
						'defaults' => ['presenter' => 'Admin:Home', 'action' => 'default'],
						'mask' => '',
						'params' => null,
						'error' => null,
					],
					(object) [
						'matched' => 'no',
						'class' => 'Nette\Application\Routers\Route',
						'defaults' => ['module' => 'Admin', 'action' => 'default'],
						'mask' => '<presenter>/<action=default>',
						'params' => null,
						'error' => null,
					],
				],
			],
			(object) [
				'matched' => 'no',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['presenter' => 'Article', 'action' => 'homepage'],
				'mask' => '',
				'params' => null,
				'error' => null,
			],
			(object) [
				'matched' => 'yes',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['action' => 'default'],
				'mask' => '<presenter>/<action=default>',
				'params' => ['presenter' => 'Foo', 'action' => 'default'],
				'error' => null,
			],
		],
	], $res);
});


test('URL: /admin', function () use ($router) {
	$httpRequest = new Http\Request(new UrlScript('/admin', '/'));
	$panel = new RoutingPanel($router, $httpRequest, new PresenterFactory(function () {}));

	$res = Assert::with($panel, fn() => $this->analyse($router, $httpRequest));

	Assert::equal([
		'path' => null,
		'domain' => null,
		'module' => null,
		'routes' => [
			(object) [
				'matched' => 'no',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['presenter' => 'Sign', 'action' => 'in'],
				'mask' => 'sign-in',
				'params' => null,
				'error' => null,
			],
			[
				'path' => 'admin/',
				'domain' => null,
				'module' => null,
				'routes' => [
					(object) [
						'matched' => 'yes',
						'class' => 'Nette\Application\Routers\Route',
						'defaults' => ['presenter' => 'Admin:Home', 'action' => 'default'],
						'mask' => '',
						'params' => ['presenter' => 'Admin:Home', 'action' => 'default'],
						'error' => null,
					],
					(object) [
						'matched' => 'no',
						'class' => 'Nette\Application\Routers\Route',
						'defaults' => ['module' => 'Admin', 'action' => 'default'],
						'mask' => '<presenter>/<action=default>',
						'params' => null,
						'error' => null,
					],
				],
			],
			(object) [
				'matched' => 'no',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['presenter' => 'Article', 'action' => 'homepage'],
				'mask' => '',
				'params' => null,
				'error' => null,
			],
			(object) [
				'matched' => 'may',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['action' => 'default'],
				'mask' => '<presenter>/<action=default>',
				'params' => ['presenter' => 'Admin', 'action' => 'default'],
				'error' => null,
			],
		],
	], $res);
});


test('URL: /admin/', function () use ($router) {
	$httpRequest = new Http\Request(new UrlScript('/admin/', '/'));
	$panel = new RoutingPanel($router, $httpRequest, new PresenterFactory(function () {}));

	$res = Assert::with($panel, fn() => $this->analyse($router, $httpRequest));

	Assert::equal([
		'path' => null,
		'domain' => null,
		'module' => null,
		'routes' => [
			(object) [
				'matched' => 'no',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['presenter' => 'Sign', 'action' => 'in'],
				'mask' => 'sign-in',
				'params' => null,
				'error' => null,
			],
			[
				'path' => 'admin/',
				'domain' => null,
				'module' => null,
				'routes' => [
					(object) [
						'matched' => 'yes',
						'class' => 'Nette\Application\Routers\Route',
						'defaults' => ['presenter' => 'Admin:Home', 'action' => 'default'],
						'mask' => '',
						'params' => ['presenter' => 'Admin:Home', 'action' => 'default'],
						'error' => null,
					],
					(object) [
						'matched' => 'no',
						'class' => 'Nette\Application\Routers\Route',
						'defaults' => ['module' => 'Admin', 'action' => 'default'],
						'mask' => '<presenter>/<action=default>',
						'params' => null,
						'error' => null,
					],
				],
			],
			(object) [
				'matched' => 'no',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['presenter' => 'Article', 'action' => 'homepage'],
				'mask' => '',
				'params' => null,
				'error' => null,
			],
			(object) [
				'matched' => 'may',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['action' => 'default'],
				'mask' => '<presenter>/<action=default>',
				'params' => ['presenter' => 'Admin', 'action' => 'default'],
				'error' => null,
			],
		],
	], $res);
});


test('URL: /admin/foo', function () use ($router) {
	$httpRequest = new Http\Request(new UrlScript('/admin/foo', '/'));
	$panel = new RoutingPanel($router, $httpRequest, new PresenterFactory(function () {}));

	$res = Assert::with($panel, fn() => $this->analyse($router, $httpRequest));

	Assert::equal([
		'path' => null,
		'domain' => null,
		'module' => null,
		'routes' => [
			(object) [
				'matched' => 'no',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['presenter' => 'Sign', 'action' => 'in'],
				'mask' => 'sign-in',
				'params' => null,
				'error' => null,
			],
			[
				'path' => 'admin/',
				'domain' => null,
				'module' => null,
				'routes' => [
					(object) [
						'matched' => 'no',
						'class' => 'Nette\Application\Routers\Route',
						'defaults' => ['presenter' => 'Admin:Home', 'action' => 'default'],
						'mask' => '',
						'params' => null,
						'error' => null,
					],
					(object) [
						'matched' => 'yes',
						'class' => 'Nette\Application\Routers\Route',
						'defaults' => ['module' => 'Admin', 'action' => 'default'],
						'mask' => '<presenter>/<action=default>',
						'params' => ['presenter' => 'Admin:Foo', 'action' => 'default'],
						'error' => null,
					],
				],
			],
			(object) [
				'matched' => 'no',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['presenter' => 'Article', 'action' => 'homepage'],
				'mask' => '',
				'params' => null,
				'error' => null,
			],
			(object) [
				'matched' => 'may',
				'class' => 'Nette\Application\Routers\Route',
				'defaults' => ['action' => 'default'],
				'mask' => '<presenter>/<action=default>',
				'params' => ['presenter' => 'Admin', 'action' => 'foo'],
				'error' => null,
			],
		],
	], $res);
});
