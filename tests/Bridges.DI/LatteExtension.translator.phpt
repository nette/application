<?php

/**
 * Test: LatteExtension passes Translator to TranslatorExtension
 */

declare(strict_types=1);

use Nette\DI;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


class Translator implements Nette\Localization\Translator
{
	public function translate(Stringable|string $message, ...$parameters): string|Stringable
	{
		return '';
	}
}

$loader = new DI\Config\Loader;
$config = $loader->load(Tester\FileMock::create('
latte:
	extensions:
		- Latte\Essential\TranslatorExtension

services:
	- Translator
', 'neon'));

$compiler = new DI\Compiler;
$compiler->addExtension('latte', new Nette\Bridges\ApplicationDI\LatteExtension(''));
$code = $compiler->addConfig($config)->compile();
eval($code);

$container = new Container;

$latte = $container->getService('nette.latteFactory')->create();
$extensions = Assert::with($latte, fn() => $this->extensions);
Assert::equal(new Latte\Essential\TranslatorExtension(new Translator), $extensions[3]);
