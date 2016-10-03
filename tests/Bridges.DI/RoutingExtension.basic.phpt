<?php

/**
 * Test: RoutingExtension.
 */

use Nette\DI;
use Nette\Bridges\ApplicationDI\RoutingExtension;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Route extends Nette\Application\Routers\Route
{}


test(function () {
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	routing:
		routes:
			index.php: Homepage:default
			item/<id>: Homepage:detail
	', 'neon'));

	$compiler = new DI\Compiler;
	$compiler->addExtension('routing', new RoutingExtension(FALSE));
	$code = $compiler->addConfig($config)->setClassName('Container_basic')->compile();
	eval($code);

	$container = new Container_basic;
	$router = $container->getService('router');
	Assert::type(Nette\Application\Routers\RouteList::class, $router);
	Assert::count(2, $router);
	Assert::same('index.php', $router[0]->getMask());
	Assert::same('item/<id>', $router[1]->getMask());

	Assert::type(Nette\Application\Routers\RouteList::class, $router);
	Assert::type(Nette\Application\Routers\Route::class, $router[0]);
});


test(function () {
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	routing:
		routeClass:
			Route
		routes:
			item/<id>: Homepage:detail
	', 'neon'));

	$compiler = new DI\Compiler;
	$compiler->addExtension('routing', new RoutingExtension(FALSE));
	$code = $compiler->addConfig($config)->setClassName('Container_customRoute')->compile();
	eval($code);

	$container = new Container_customRoute;
	$router = $container->getService('router');

	Assert::type(Nette\Application\Routers\RouteList::class, $router);
	Assert::type(Route::class, $router[0]);
});
