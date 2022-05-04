<?php

/**
 * Test: renderSnippets and control wrapped in a snippet
 */

declare(strict_types=1);

use Nette\Http;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


class TestControl extends Nette\Application\UI\Control
{
	public function render()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($this));
		$latte->render('{snippet foo}hello{/snippet}');
	}
}

class TestPresenter extends Nette\Application\UI\Presenter
{
	public function createComponentTest()
	{
		return new TestControl;
	}


	public function render()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($this));
		$latte->render('{snippet foo}{control test}{/snippet}');
	}
}


$presenter = new TestPresenter;
$presenter->injectPrimary(new Http\Request(new Http\UrlScript('/')), new Http\Response);
$presenter->snippetMode = true;
$presenter->redrawControl('foo');
$presenter['test']->redrawControl('foo');
$presenter->render();
Assert::same([
	'snippets' => [
		'snippet--foo' => '<div id="snippet-test-foo">hello</div>',
	],
], (array) $presenter->payload);


$presenter = new TestPresenter;
$presenter->injectPrimary(new Http\Request(new Http\UrlScript('/')), new Http\Response);
$presenter->snippetMode = true;
$presenter['test']->redrawControl('foo');
$presenter->render();
Assert::same([
	'snippets' => [
		'snippet-test-foo' => 'hello',
	],
], (array) $presenter->payload);
