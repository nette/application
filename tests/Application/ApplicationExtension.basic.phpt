<?php

/**
 * Test: ApplicationExtension
 */

use Nette\DI,
	Nette\Bridges\ApplicationDI\ApplicationExtension,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$compiler = new DI\Compiler;
	$compiler->addExtension('application', new ApplicationExtension(FALSE));

	$builder = $compiler->getContainerBuilder();
	$builder->addDefinition('myRouter')->setClass('Nette\Application\Routers\SimpleRouter');
	$builder->addDefinition('myHttpRequest')->setFactory('Nette\Http\Request', array(new DI\Statement('Nette\Http\UrlScript')));
	$builder->addDefinition('myHttpResponse')->setClass('Nette\Http\Response');

	$code = $compiler->compile(array(
		'application' => array('debugger' => FALSE),
	), 'Container1');

	file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
	require TEMP_DIR . '/code.php';

	$container = new Container1;
	Assert::type( 'Nette\Application\Application', $container->getService('application') );
	Assert::type( 'Nette\Application\PresenterFactory', $container->getService('nette.presenterFactory') );
});
