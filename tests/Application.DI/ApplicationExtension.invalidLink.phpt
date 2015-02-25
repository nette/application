<?php

/**
 * Test: ApplicationExtension
 */

use Nette\DI,
	Nette\Bridges\ApplicationDI\ApplicationExtension,
	Nette\Application\UI\Presenter,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/files/MyPresenter.php';


function createCompiler($config)
{
	$compiler = new DI\Compiler;
	$compiler->loadConfig(Tester\FileMock::create($config, 'neon'));
	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass('Nette\Application\Routers\SimpleRouter');
	$builder->addDefinition('myHttpRequest')->setFactory('Nette\Http\Request', array(new DI\Statement('Nette\Http\UrlScript')));
	$builder->addDefinition('myHttpResponse')->setClass('Nette\Http\Response');
	return $compiler;
}


test(function() {
	$compiler = createCompiler('
	application:
		debugger: no
		silentLinks: yes

	services:
		presenter: Presenter1
	');
	$compiler->addExtension('application', new ApplicationExtension(TRUE));
	$code = $compiler->compile(NULL, 'Container4');
	eval($code);

	$container = new Container4;
	Assert::same(
		Presenter::INVALID_LINK_TEXTUAL,
		$container->getService('presenter')->invalidLinkMode
	);
});


test(function() {
	$compiler = createCompiler('
	application:
		debugger: no
		silentLinks: no

	services:
		presenter: Presenter1
	');
	$compiler->addExtension('application', new ApplicationExtension(TRUE));
	$code = $compiler->compile(NULL, 'Container5');
	eval($code);

	$container = new Container5;
	Assert::same(
		Presenter::INVALID_LINK_WARNING | Presenter::INVALID_LINK_TEXTUAL,
		$container->getService('presenter')->invalidLinkMode
	);
});


test(function() {
	$compiler = createCompiler('
	application:
		debugger: no
		silentLinks: yes

	services:
		presenter: Presenter1
	');
	$compiler->addExtension('application', new ApplicationExtension(FALSE));
	$code = $compiler->compile(NULL, 'Container6');
	eval($code);

	$container = new Container6;
	Assert::same(
		Presenter::INVALID_LINK_WARNING,
		$container->getService('presenter')->invalidLinkMode
	);
});


test(function() {
	$compiler = createCompiler('
	application:
		debugger: no
		silentLinks: no

	services:
		presenter: Presenter1
	');
	$compiler->addExtension('application', new ApplicationExtension(FALSE));
	$code = $compiler->compile(NULL, 'Container7');
	eval($code);

	$container = new Container7;
	Assert::same(
		Presenter::INVALID_LINK_WARNING,
		$container->getService('presenter')->invalidLinkMode
	);
});
