<?php

/**
 * Test: LatteExtension.
 */

declare(strict_types=1);

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


class NonStaticMacrosFactory
{

	/** @var string */
	private $parameter;


	public function __construct($parameter)
	{
		$this->parameter = $parameter;
	}


	public function install(Latte\Compiler $compiler)
	{
		$macros = new Latte\Macros\MacroSet($compiler);
		$macros->addMacro('foo', 'foo ' . $this->parameter);
		Notes::add(get_class($this) . '::install');
	}


	public function create(Latte\Compiler $compiler)
	{
		$macros = new Latte\Macros\MacroSet($compiler);
		$macros->addMacro('foo2', 'foo ' . $this->parameter);
		Notes::add(get_class($this) . '::create');
	}
}


class AnotherExtension extends Nette\DI\CompilerExtension
{

	public function beforeCompile()
	{
		foreach ($this->compiler->getExtensions(Nette\Bridges\ApplicationDI\LatteExtension::class) as $extension) {
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
		- @macroFactory
		- @macroFactory::create

services:
	macroFactory: NonStaticMacrosFactory(foo)
', 'neon'));

$compiler = new DI\Compiler;
$compiler->addExtension('latte', new Nette\Bridges\ApplicationDI\LatteExtension(NULL, FALSE));
$compiler->addExtension('another', new AnotherExtension);
$code = $compiler->addConfig($config)->compile();
eval($code);

$container = new Container;


Assert::type(Nette\Bridges\ApplicationLatte\ILatteFactory::class, $container->getService('nette.latteFactory'));
$container->getService('nette.latteFactory')->create()->setLoader(new Latte\Loaders\StringLoader)->compile('');

Assert::same([
	'LoremIpsumMacros',
	'IpsumLoremMacros',
	'NonStaticMacrosFactory::install',
	'NonStaticMacrosFactory::create',
	'FooMacros',
], Notes::fetch());
