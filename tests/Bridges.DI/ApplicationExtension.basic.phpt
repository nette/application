<?php

/**
 * Test: ApplicationExtension
 */

declare(strict_types=1);

use Nette\DI;
use Nette\Bridges\ApplicationDI\ApplicationExtension;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension(FALSE));

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass(Nette\Application\Routers\SimpleRouter::class);
	$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new DI\Statement(Nette\Http\UrlScript::class)]);
	$builder->addDefinition('myHttpResponse')->setClass(Nette\Http\Response::class);

	$code = $compiler->setClassName('Container1')->compile();
	eval($code);

	$container = new Container1;
	Assert::type(Nette\Application\Application::class, $container->getService('application'));
	Assert::type(Nette\Application\PresenterFactory::class, $container->getService('nette.presenterFactory'));
	Assert::type(Nette\Application\LinkGenerator::class, $container->getService('application.linkGenerator'));
});
