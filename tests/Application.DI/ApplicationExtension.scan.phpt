<?php

/**
 * Test: ApplicationExtension
 */

use Nette\DI;
use Nette\Bridges\ApplicationDI\ApplicationExtension;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/files/MyPresenter.php';


test(function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension);

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass(Nette\Application\Routers\SimpleRouter::class);
	$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new DI\Statement(Nette\Http\UrlScript::class)]);
	$builder->addDefinition('myHttpResponse')->setClass(Nette\Http\Response::class);
	$code = $compiler->compile([
		'application' => ['debugger' => FALSE],
	], 'Container1');
	eval($code);

	$container = new Container1;
	$tags = $container->findByTag('nette.presenter');
	Assert::count(1, array_keys($tags, 'NetteModule\ErrorPresenter'));
	Assert::count(1, array_keys($tags, 'NetteModule\MicroPresenter'));
	Assert::count(0, array_keys($tags, Nette\Application\UI\Presenter::class));
});


test(function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension);

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass(Nette\Application\Routers\SimpleRouter::class);
	$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new DI\Statement(Nette\Http\UrlScript::class)]);
	$builder->addDefinition('myHttpResponse')->setClass(Nette\Http\Response::class);
	$code = $compiler->compile([
		'application' => [
			'scanDirs' => [__DIR__ . '/files'],
			'debugger' => FALSE,
		],
	], 'Container2');
	eval($code);

	$container = new Container2;
	$tags = $container->findByTag('nette.presenter');
	Assert::count(1, array_keys($tags, 'BasePresenter'));
	Assert::count(1, array_keys($tags, 'Presenter1'));
	Assert::count(1, array_keys($tags, 'Presenter2'));
});


test(function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension(FALSE, [__DIR__ . '/files']));

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass(Nette\Application\Routers\SimpleRouter::class);
	$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new DI\Statement(Nette\Http\UrlScript::class)]);
	$builder->addDefinition('myHttpResponse')->setClass(Nette\Http\Response::class);
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	application:
		debugger: no

	services:
		-
			factory: Presenter1
			setup:
				- setView(test)
	', 'neon'));
	$code = $compiler->compile($config, 'Container3');
	eval($code);

	$container = new Container3;
	$tags = $container->findByTag('nette.presenter');
	Assert::count(1, array_keys($tags, 'BasePresenter'));
	Assert::count(1, array_keys($tags, 'Presenter1'));
	Assert::count(1, array_keys($tags, 'Presenter2'));

	$tmp = array_keys($tags, 'Presenter1');
	Assert::same('test', $container->getService($tmp[0])->getView());
});
