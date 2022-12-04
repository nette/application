<?php

declare(strict_types=1);

use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationDI\ApplicationExtension;
use Nette\DI;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/files/MyPresenter.php';


function createCompiler(string $config): DI\Compiler
{
	$compiler = new DI\Compiler;
	$compiler->loadConfig(Tester\FileMock::create($config, 'neon'));
	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setFactory(Nette\Application\Routers\SimpleRouter::class);
	$builder->addDefinition('myHttpRequest')->setFactory(Nette\Http\Request::class, [new DI\Definitions\Statement(Nette\Http\UrlScript::class)]);
	$builder->addDefinition('myHttpResponse')->setFactory(Nette\Http\Response::class);
	return $compiler;
}


test('', function () {
	$compiler = createCompiler('
	application:
		silentLinks: yes

	services:
		presenter: Presenter1
	');
	$compiler->addExtension('application', new ApplicationExtension(true));
	$code = $compiler->setClassName('Container4')->compile();
	eval($code);

	$container = new Container4;
	Assert::same(
		Presenter::InvalidLinkTextual,
		$container->getService('presenter')->invalidLinkMode,
	);
});


test('', function () {
	$compiler = createCompiler('
	application:
		silentLinks: no

	services:
		presenter: Presenter1
	');
	$compiler->addExtension('application', new ApplicationExtension(true));
	$code = $compiler->setClassName('Container5')->compile();
	eval($code);

	$container = new Container5;
	Assert::same(
		Presenter::InvalidLinkWarning | Presenter::InvalidLinkTextual,
		$container->getService('presenter')->invalidLinkMode,
	);
});


test('', function () {
	$compiler = createCompiler('
	application:
		silentLinks: yes

	services:
		presenter: Presenter1
	');
	$compiler->addExtension('application', new ApplicationExtension(false));
	$code = $compiler->setClassName('Container6')->compile();
	eval($code);

	$container = new Container6;
	Assert::same(
		Presenter::InvalidLinkWarning,
		$container->getService('presenter')->invalidLinkMode,
	);
});


test('', function () {
	$compiler = createCompiler('
	application:
		silentLinks: no

	services:
		presenter: Presenter1
	');
	$compiler->addExtension('application', new ApplicationExtension(false));
	$code = $compiler->setClassName('Container7')->compile();
	eval($code);

	$container = new Container7;
	Assert::same(
		Presenter::InvalidLinkWarning,
		$container->getService('presenter')->invalidLinkMode,
	);
});
