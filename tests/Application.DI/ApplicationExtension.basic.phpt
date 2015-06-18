<?php

/**
 * Test: ApplicationExtension
 */

use Nette\DI;
use Nette\Bridges\ApplicationDI\ApplicationExtension;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension(FALSE));

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass('Nette\Application\Routers\SimpleRouter');
	$builder->addDefinition('myHttpRequest')->setFactory('Nette\Http\Request', [new DI\Statement('Nette\Http\UrlScript')]);
	$builder->addDefinition('myHttpResponse')->setClass('Nette\Http\Response');

	$code = $compiler->compile([
		'application' => ['debugger' => FALSE],
	], 'Container1');
	eval($code);

	$container = new Container1;
	Assert::type('Nette\Application\Application', $container->getService('application'));
	Assert::type('Nette\Application\PresenterFactory', $container->getService('nette.presenterFactory'));
	Assert::type('Nette\Application\LinkGenerator', $container->getService('application.linkGenerator'));
});
