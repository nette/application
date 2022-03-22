<?php

/**
 * Test: UIMacros and n:snippet and custom HTML attribute.
 */

declare(strict_types=1);

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '>')) {
	Tester\Environment::skip('Test for Latte 2');
}


class TestPresenter extends Nette\Application\UI\Presenter
{
	public function render(string $template)
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		UIMacros::install($latte->getCompiler());
		$latte->addProvider('snippetBridge', new Nette\Bridges\ApplicationLatte\SnippetBridge($this));
		$latte->onCompile[] = function ($latte) {
			$latte->getCompiler()->getMacros()['snippet'][0]->snippetAttribute = 'data-snippet';
		};
		$latte->render($template);
	}
}


$presenter = new TestPresenter;
ob_start();
$presenter->render('<div n:snippet=test>hello</div>');
$content = ob_get_clean();
Assert::same('<div data-snippet="snippet--test">hello</div>', $content);


$presenter = new TestPresenter;
Assert::exception(function () use ($presenter) {
	$presenter->render('<div n:snippet=test data-snippet>hello</div>');
}, Latte\CompileException::class, 'Cannot combine HTML attribute data-snippet with n:snippet.');
