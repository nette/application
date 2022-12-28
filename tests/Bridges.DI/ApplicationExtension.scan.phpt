<?php

declare(strict_types=1);

use Nette\Bridges\ApplicationDI\ApplicationExtension;
use Nette\DI;
use Nette\DI\Definitions\Statement;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/files/MyPresenter.php';


test('', function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension);

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setFactory(Nette\Application\Routers\SimpleRouter::class);
	$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new Statement(Nette\Http\UrlScript::class)]);
	$builder->addDefinition('myHttpResponse')->setFactory(Nette\Http\Response::class);
	$code = $compiler->setClassName('Container1')->compile();
	eval($code);

	$container = new Container1;
	Assert::count(1, $container->findByType(NetteModule\ErrorPresenter::class));
	Assert::count(1, $container->findByType(NetteModule\MicroPresenter::class));
	Assert::count(0, $container->findByType(Nette\Application\UI\Presenter::class));
});


test('', function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension);

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setFactory(Nette\Application\Routers\SimpleRouter::class);
	$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new Statement(Nette\Http\UrlScript::class)]);
	$builder->addDefinition('myHttpResponse')->setFactory(Nette\Http\Response::class);
	$code = $compiler->addConfig([
		'application' => [
			'scanDirs' => [__DIR__ . '/files'],
			'scanFilter' => '*Presenter*',
		],
	])->setClassName('Container2')->compile();
	eval($code);

	$container = new Container2;
	Assert::count(3, $container->findByType(BasePresenter::class));
	Assert::count(1, $container->findByType(Presenter1::class));
	Assert::count(1, $container->findByType(Presenter2::class));
});


test('', function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension(false, [__DIR__ . '/files']));

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setFactory(Nette\Application\Routers\SimpleRouter::class);
	$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new Statement(Nette\Http\UrlScript::class)]);
	$builder->addDefinition('myHttpResponse')->setFactory(Nette\Http\Response::class);
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	application:
		scanFilter: *Presenter*

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

	$name = $container->findByType(Presenter1::class)[0];
	Assert::same('test', $container->createService($name)->getView());
});


test('', function () {
	$robot = new Nette\Loaders\RobotLoader;
	$robot->addDirectory(__DIR__ . '/files');
	$robot->setTempDirectory(getTempDir());

	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension(false, null, null, $robot));

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setFactory(Nette\Application\Routers\SimpleRouter::class);
	$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new Statement(Nette\Http\UrlScript::class)]);
	$builder->addDefinition('myHttpResponse')->setFactory(Nette\Http\Response::class);
	$code = $compiler->setClassName('Container4')->compile();
	eval($code);

	$container = new Container2;
	Assert::count(3, $container->findByType(BasePresenter::class));
	Assert::count(1, $container->findByType(Presenter1::class));
	Assert::count(1, $container->findByType(Presenter2::class));
});
