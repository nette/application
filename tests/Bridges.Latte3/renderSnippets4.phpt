<?php

/**
 * Test: renderSnippets and template rendered from another template
 * @phpVersion 8.0
 */

declare(strict_types=1);

use Nette\Http;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


class TestPresenter extends Nette\Application\UI\Presenter
{
	public function render()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($this));
		$latte->render('{snippet foo}{php $presenter->renderFoo()}{/snippet}', ['presenter' => $this]);
	}


	public function renderFoo()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($this));
		$latte->render('Hello');
	}
}


$presenter = new TestPresenter;
$presenter->injectPrimary(null, null, null, new Http\Request(new Http\UrlScript('/')), new Http\Response);
$presenter->snippetMode = true;
$presenter->redrawControl('foo');
$presenter->render();
Assert::same([
	'snippets' => [
		'snippet--foo' => 'Hello',
	],
], (array) $presenter->payload);
