<?php

/**
 * Test: RoutingExtension.
 */

declare(strict_types=1);

use Nette\Bridges\ApplicationDI\RoutingExtension;
use Nette\DI;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('', function () {
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	routing:
		routes:
			index.php: Homepage:default
			item/<id>: Homepage:detail
	', 'neon'));

	$compiler = new DI\Compiler;
	$compiler->addExtension('routing', new RoutingExtension(false));
	$code = $compiler->addConfig($config)->setClassName('Container1')->compile();
	eval($code);

	$container = new Container1;
	$router = $container->getService('router');
	Assert::type(Nette\Application\Routers\RouteList::class, $router);
	Assert::same('index.php', $router[0]->getMask());
	Assert::same('item/<id>', $router[1]->getMask());

	Assert::type(Nette\Application\Routers\RouteList::class, $router);
	Assert::type(Nette\Application\Routers\Route::class, $router[0]);
});
