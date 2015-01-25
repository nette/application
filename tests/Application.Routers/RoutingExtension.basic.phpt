<?php

/**
 * Test: RoutingExtension.
 */

use Nette\DI,
	Nette\Bridges\ApplicationDI\RoutingExtension,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	routing:
		routes:
			index.php: Homepage:default
			item/<id>: Homepage:detail
	', 'neon'));

	$compiler = new DI\Compiler;
	$compiler->addExtension('routing', new RoutingExtension(FALSE));
	$code = $compiler->compile($config, 'Container1');
	eval($code);

	$container = new Container1;
	$router = $container->getService('router');
	Assert::type('Nette\Application\Routers\RouteList', $router);
	Assert::same(2, count($router));
	Assert::same('index.php', $router[0]->getMask());
	Assert::same('item/<id>', $router[1]->getMask());
});
