<?php

/**
 * Test: ApplicationExtension
 */

use Nette\DI,
	Nette\Bridges\ApplicationDI\ApplicationExtension,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/files/MyPresenter.php';


test(function() {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension);

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass('Nette\Application\Routers\SimpleRouter');
	$builder->addDefinition('myHttpRequest')->setFactory('Nette\Http\Request', array(new DI\Statement('Nette\Http\UrlScript')));
	$builder->addDefinition('myHttpResponse')->setClass('Nette\Http\Response');
	$code = $compiler->compile(array(
		'application' => array('debugger' => FALSE),
	), 'Container1');
	eval($code);

	$container = new Container1;
	$tags = $container->findByTag('nette.presenter');
	Assert::count( 1, array_keys($tags, 'NetteModule\ErrorPresenter') );
	Assert::count( 1, array_keys($tags, 'NetteModule\MicroPresenter') );
	Assert::count( 0, array_keys($tags, 'Nette\Application\UI\Presenter') );
});


test(function() {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension);

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass('Nette\Application\Routers\SimpleRouter');
	$builder->addDefinition('myHttpRequest')->setFactory('Nette\Http\Request', array(new DI\Statement('Nette\Http\UrlScript')));
	$builder->addDefinition('myHttpResponse')->setClass('Nette\Http\Response');
	$code = $compiler->compile(array(
		'application' => array(
			'scanDirs' => array(__DIR__ . '/files'),
			'debugger' => FALSE,
		),
	), 'Container2');
	eval($code);

	$container = new Container2;
	$tags = $container->findByTag('nette.presenter');
	Assert::count( 1, array_keys($tags, 'BasePresenter') );
	Assert::count( 1, array_keys($tags, 'Presenter1') );
	Assert::count( 1, array_keys($tags, 'Presenter2') );
});


test(function() {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension(FALSE, array(__DIR__ . '/files')));

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass('Nette\Application\Routers\SimpleRouter');
	$builder->addDefinition('myHttpRequest')->setFactory('Nette\Http\Request', array(new DI\Statement('Nette\Http\UrlScript')));
	$builder->addDefinition('myHttpResponse')->setClass('Nette\Http\Response');
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
	Assert::count( 1, array_keys($tags, 'BasePresenter') );
	Assert::count( 1, array_keys($tags, 'Presenter1') );
	Assert::count( 1, array_keys($tags, 'Presenter2') );

	$tmp = array_keys($tags, 'Presenter1');
	Assert::same( 'test', $container->getService($tmp[0])->getView() );
});


test(function() {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension(TRUE));

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass('Nette\Application\Routers\SimpleRouter');
	$builder->addDefinition('myHttpRequest')->setFactory('Nette\Http\Request', array(new DI\Statement('Nette\Http\UrlScript')));
	$builder->addDefinition('myHttpResponse')->setClass('Nette\Http\Response');
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	application:
		debugger: no
		silentInvalidLinks: yes

	services:
		-
			factory: Presenter1
			setup:
				- setView(test)
	', 'neon'));
	$code = $compiler->compile($config, 'Container4');
	file_put_contents('container.php', $code);
	eval($code);

	/** @var Nette\DI\Container $container */
	$container = new Container4;
	$tags = $container->findByTag('nette.presenter');
	Assert::count( 1, array_keys($tags, 'Presenter1') );
	$tmp = array_keys($tags, 'Presenter1');
	$presenter = $container->getService($tmp[0]);
	Assert::same( \Nette\Application\UI\Presenter::INVALID_LINK_VISUAL, $presenter->invalidLinkMode );
});


test(function() {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension(TRUE));

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass('Nette\Application\Routers\SimpleRouter');
	$builder->addDefinition('myHttpRequest')->setFactory('Nette\Http\Request', array(new DI\Statement('Nette\Http\UrlScript')));
	$builder->addDefinition('myHttpResponse')->setClass('Nette\Http\Response');
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	application:
		debugger: no
		silentInvalidLinks: no

	services:
		-
			factory: Presenter1
			setup:
				- setView(test)
	', 'neon'));
	$code = $compiler->compile($config, 'Container5');
	file_put_contents('container.php', $code);
	eval($code);

	/** @var Nette\DI\Container $container */
	$container = new Container5;
	$tags = $container->findByTag('nette.presenter');
	Assert::count( 1, array_keys($tags, 'Presenter1') );
	$tmp = array_keys($tags, 'Presenter1');
	$presenter = $container->getService($tmp[0]);
	Assert::same( \Nette\Application\UI\Presenter::INVALID_LINK_WARNING, $presenter->invalidLinkMode );
});


test(function() {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension(FALSE));

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass('Nette\Application\Routers\SimpleRouter');
	$builder->addDefinition('myHttpRequest')->setFactory('Nette\Http\Request', array(new DI\Statement('Nette\Http\UrlScript')));
	$builder->addDefinition('myHttpResponse')->setClass('Nette\Http\Response');
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	application:
		debugger: no
		silentInvalidLinks: yes

	services:
		-
			factory: Presenter1
			setup:
				- setView(test)
	', 'neon'));
	$code = $compiler->compile($config, 'Container6');
	file_put_contents('container.php', $code);
	eval($code);

	/** @var Nette\DI\Container $container */
	$container = new Container6;
	$tags = $container->findByTag('nette.presenter');
	Assert::count( 1, array_keys($tags, 'Presenter1') );
	$tmp = array_keys($tags, 'Presenter1');
	$presenter = $container->getService($tmp[0]);
	Assert::same( \Nette\Application\UI\Presenter::INVALID_LINK_SILENT, $presenter->invalidLinkMode );
});


test(function() {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension(FALSE));

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass('Nette\Application\Routers\SimpleRouter');
	$builder->addDefinition('myHttpRequest')->setFactory('Nette\Http\Request', array(new DI\Statement('Nette\Http\UrlScript')));
	$builder->addDefinition('myHttpResponse')->setClass('Nette\Http\Response');
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	application:
		debugger: no
		silentInvalidLinks: no

	services:
		-
			factory: Presenter1
			setup:
				- setView(test)
	', 'neon'));
	$code = $compiler->compile($config, 'Container7');
	file_put_contents('container.php', $code);
	eval($code);

	/** @var Nette\DI\Container $container */
	$container = new Container7;
	$tags = $container->findByTag('nette.presenter');
	Assert::count( 1, array_keys($tags, 'Presenter1') );
	$tmp = array_keys($tags, 'Presenter1');
	$presenter = $container->getService($tmp[0]);
	Assert::same( \Nette\Application\UI\Presenter::INVALID_LINK_SILENT, $presenter->invalidLinkMode );
});
