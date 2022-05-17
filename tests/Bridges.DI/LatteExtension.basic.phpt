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


class MyExtension extends Latte\Extension
{
	public $arg;


	public function __construct($arg = null)
	{
		$this->arg = $arg;
	}
}


class AnotherExtension extends Nette\DI\CompilerExtension
{
	public function beforeCompile()
	{
		foreach ($this->compiler->getExtensions(Nette\Bridges\ApplicationDI\LatteExtension::class) as $extension) {
			$extension->addExtension('MyExtension');
		}
	}
}

$loader = new DI\Config\Loader;
$config = $loader->load(Tester\FileMock::create('
latte:
	extensions:
		- MyExtension
		- MyExtension(1)
		- @latteExt

services:
	latteExt: MyExtension(2)
', 'neon'));

$compiler = new DI\Compiler;
$compiler->addExtension('latte', new Nette\Bridges\ApplicationDI\LatteExtension('', false));
$compiler->addExtension('another', new AnotherExtension);
$code = $compiler->addConfig($config)->compile();
eval($code);

$container = new Container;


Assert::type(Nette\Bridges\ApplicationLatte\LatteFactory::class, $container->getService('nette.latteFactory'));
$latte = $container->getService('nette.latteFactory')->create();
$extensions = Assert::with($latte, fn() => $this->extensions);

Assert::equal([
	new Latte\Essential\CoreExtension,
	new Latte\Sandbox\SandboxExtension,
	new MyExtension,
	new MyExtension(1),
	new MyExtension(2),
	new MyExtension,
], $extensions);
