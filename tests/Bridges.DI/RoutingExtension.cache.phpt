<?php

/**
 * Test: RoutingExtension caching.
 */

use Nette\Application\Routers\Route;
use Nette\Bridges\ApplicationDI\RoutingExtension;
use Nette\DI;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MyRouter implements Nette\Application\IRouter
{
	public $woken;


	function match(Nette\Http\IRequest $httpRequest)
	{
	}


	function constructUrl(Nette\Application\Request $appRequest, Nette\Http\Url $refUrl)
	{
	}


	function __wakeup()
	{
		$this->woken = true;
	}
}


test(function () {
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	services:
		router: MyRouter
	', 'neon'));

	$compiler = new DI\Compiler;
	$compiler->addExtension('routing', new RoutingExtension(false));
	$code = $compiler->addConfig($config)->setClassName('Container1')->compile();
	eval($code);

	$container = new Container1;
	Assert::type(MyRouter::class, $container->getService('router'));
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
	$compiler->addExtension('routing', new RoutingExtension(false));
	$code = $compiler->addConfig($config)->setClassName('Container2')->compile();
	eval($code);

	$container = new Container2;
	Assert::type(MyRouter::class, $container->getService('router'));
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
	$compiler->addExtension('routing', new RoutingExtension(false));
	$code = $compiler->addConfig($config)->setClassName('Container3')->compile();
}, Nette\DI\ServiceCreationException::class, 'Unable to cache router due to error: %a%');
