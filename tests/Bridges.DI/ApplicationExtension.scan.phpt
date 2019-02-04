<?php

declare(strict_types=1);

use Nette\Bridges\ApplicationDI\ApplicationExtension;
use Nette\DI;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/files/MyPresenter.php';


test(function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension);

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setFactory(Nette\Application\Routers\SimpleRouter::class);
	$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new DI\Statement(Nette\Http\UrlScript::class)]);
	$builder->addDefinition('myHttpResponse')->setFactory(Nette\Http\Response::class);
	$code = $compiler->setClassName('Container1')->compile();
	eval($code);

	$container = new Container1;
	Assert::count(1, $container->findByType(NetteModule\ErrorPresenter::class));
	Assert::count(1, $container->findByType(NetteModule\MicroPresenter::class));
	Assert::count(0, $container->findByType(Nette\Application\UI\Presenter::class));
});


test(function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension);

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setFactory(Nette\Application\Routers\SimpleRouter::class);
	$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new DI\Statement(Nette\Http\UrlScript::class)]);
	$builder->addDefinition('myHttpResponse')->setFactory(Nette\Http\Response::class);
	$code = $compiler->addConfig([
		'application' => [
			'scanDirs' => [__DIR__ . '/files'],
		],
	])->setClassName('Container2')->compile();
	eval($code);

	$container = new Container2;
	Assert::count(3, $container->findByType(BasePresenter::class));
	Assert::count(1, $container->findByType(Presenter1::class));
	Assert::count(1, $container->findByType(Presenter2::class));
});


test(function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension(false, [__DIR__ . '/files']));

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setFactory(Nette\Application\Routers\SimpleRouter::class);
	$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new DI\Statement(Nette\Http\UrlScript::class)]);
	$builder->addDefinition('myHttpResponse')->setFactory(Nette\Http\Response::class);
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	services:
		-
			factory: Presenter1
			setup:
				- setView(test)
	', 'neon'));
	$code = $compiler->addConfig($config)->setClassName('Container3')->compile();
	eval($code);

	$container = new Container3;
	Assert::count(3, $container->findByType(BasePresenter::class));
	Assert::count(1, $container->findByType(Presenter1::class));
	Assert::count(1, $container->findByType(Presenter2::class));

	Assert::same('test', $container->getByType(Presenter1::class)->getView());
});
