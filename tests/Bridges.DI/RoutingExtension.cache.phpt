<?php

/**
 * Test: RoutingExtension caching.
 */

use Nette\DI;
use Nette\Bridges\ApplicationDI\RoutingExtension;
use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MyRouter implements Nette\Application\IRouter
{
	public $woken;

	function match(Nette\Http\IRequest $httpRequest)
	{}

	function constructUrl(Nette\Application\Request $appRequest, Nette\Http\Url $refUrl)
	{}

	function __wakeup()
	{
		$this->woken = TRUE;
	}
}


test(function () {
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	services:
		router: MyRouter
	', 'neon'));

	$compiler = new DI\Compiler;
	$compiler->addExtension('routing', new RoutingExtension(FALSE));
	$code = $compiler->compile($config, 'Container1');
	eval($code);

	$container = new Container1;
	Assert::type('MyRouter', $container->getService('router'));
	Assert::null($container->getService('router')->woken);
});


test(function () {
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	routing:
		cache: yes

	services:
		router: MyRouter
	', 'neon'));

	$compiler = new DI\Compiler;
	$compiler->addExtension('routing', new RoutingExtension(FALSE));
	$code = $compiler->compile($config, 'Container2');
	eval($code);

	$container = new Container2;
	Assert::type('MyRouter', $container->getService('router'));
	Assert::true($container->getService('router')->woken);
});


Assert::exception(function () {

	/** @return Nette\Application\IRouter */
	function myRouterFactory()
	{
		return new Route('path', function () {});
	}

	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	routing:
		cache: yes

	services:
		router: ::myRouterFactory
	', 'neon'));

	$compiler = new DI\Compiler;
	$compiler->addExtension('routing', new RoutingExtension(FALSE));
	$compiler->compile($config, 'Container3');
}, 'Nette\DI\ServiceCreationException', 'Unable to cache router due to error: %a%');
