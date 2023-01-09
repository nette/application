<?php

/**
 * Test: LatteExtension v3
 */

declare(strict_types=1);

use Nette\DI;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}

Tester\Environment::bypassFinals();


test('no variables', function () {
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	latte:
		variables:
	', 'neon'));

	$compiler = new DI\Compiler;
	$compiler->addExtension('latte', new Nette\Bridges\ApplicationDI\LatteExtension('', false));
	$code = $compiler
		->addConfig($config)
		->setClassName('Container1')
		->compile();
	eval($code);

	$container = new Container1;
	$latteFactory = $container->getService('latte.templateFactory');
	$presenter = Mockery::mock(Nette\Application\UI\Presenter::class);
	$presenter->shouldReceive('getHttpResponse')->andReturn(Mockery::mock(Nette\Http\IResponse::class)->shouldIgnoreMissing());
	$presenter->shouldIgnoreMissing();

	$template = $latteFactory->createTemplate($presenter);
	Assert::notContains('config', $template->getParameters());
});


test('presenter presence', function () {
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	latte:
		variables:
			foo: bar
	', 'neon'));

	$compiler = new DI\Compiler;
	$compiler->addExtension('latte', new Nette\Bridges\ApplicationDI\LatteExtension('', false));
	$code = $compiler
		->addConfig($config)
		->setClassName('Container2')
		->compile();
	eval($code);

	$container = new Container2;
	$latteFactory = $container->getService('latte.templateFactory');
	$template = $latteFactory->createTemplate();
	Assert::notContains('config', $template->getParameters());


	$presenter = Mockery::mock(Nette\Application\UI\Presenter::class);
	$presenter->shouldReceive('getHttpResponse')->andReturn(Mockery::mock(Nette\Http\IResponse::class)->shouldIgnoreMissing());
	$presenter->shouldIgnoreMissing();

	$template = $latteFactory->createTemplate($presenter);
	Assert::equal(
		(object) ['foo' => 'bar'],
		$template->getParameters()['config'],
	);
});
