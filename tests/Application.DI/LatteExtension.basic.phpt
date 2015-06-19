<?php

/**
 * Test: LatteExtension.
 */

use Nette\DI;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class LoremIpsumMacros extends Latte\Macros\MacroSet
{

	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('lorem', 'lorem');
		Notes::add(get_class($me));
	}

}


class IpsumLoremMacros extends Latte\Macros\MacroSet
{

	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('ipsum', 'ipsum');
		Notes::add(get_class($me));
	}

}


class FooMacros extends Latte\Macros\MacroSet
{

	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('foo', 'foo');
		Notes::add(get_class($me));
	}

}


class AnotherExtension extends Nette\DI\CompilerExtension
{

	public function beforeCompile()
	{
		foreach ($this->compiler->getExtensions('Nette\Bridges\ApplicationDI\LatteExtension') as $extension) {
			$extension->addMacro('FooMacros::install');
		}
	}

}


$loader = new DI\Config\Loader;
$config = $loader->load(Tester\FileMock::create('
latte:
	macros:
		- LoremIpsumMacros
		- IpsumLoremMacros::install
', 'neon'));

$compiler = new DI\Compiler;
$compiler->addExtension('latte', new Nette\Bridges\ApplicationDI\LatteExtension(NULL, FALSE));
$compiler->addExtension('another', new AnotherExtension);
$code = $compiler->compile($config, 'Container');
eval($code);

$container = new Container;


Assert::type('Nette\Bridges\ApplicationLatte\ILatteFactory', $container->getService('nette.latteFactory'));
$container->getService('nette.latteFactory')->create()->setLoader(new Latte\Loaders\StringLoader)->compile('');

Assert::same([
	'LoremIpsumMacros',
	'IpsumLoremMacros',
	'FooMacros',
], Notes::fetch());
