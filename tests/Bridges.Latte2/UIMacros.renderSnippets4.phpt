<?php

/**
 * Test: UIMacros, renderSnippets and template rendered from another template
 */

declare(strict_types=1);

use Nette\Bridges\ApplicationLatte\SnippetBridge;
use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '>')) {
	Tester\Environment::skip('Test for Latte 2');
}



class TestPresenter extends Nette\Application\UI\Presenter
{
	public function render()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		UIMacros::install($latte->getCompiler());
		$latte->addProvider('uiControl', $this);
		$latte->addProvider('snippetBridge', new SnippetBridge($this));
		$latte->render('{snippet foo}{php $presenter->renderFoo()}{/snippet}', ['presenter' => $this]);
	}


	public function renderFoo()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		UIMacros::install($latte->getCompiler());
		$latte->addProvider('uiControl', $this);
		$latte->addProvider('snippetBridge', new SnippetBridge($this));
		$latte->render('Hello');
	}
}


$presenter = new TestPresenter;
$presenter->snippetMode = true;
$presenter->redrawControl('foo');
$presenter->render();
Assert::same([
	'snippets' => [
		'snippet--foo' => 'Hello',
	],
], (array) $presenter->payload);
