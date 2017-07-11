<?php

/**
 * Test: UIMacros, renderSnippets and control wrapped in a snippet
 */

declare(strict_types=1);

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestControl extends Nette\Application\UI\Control
{
	public function render()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		UIMacros::install($latte->getCompiler());
		$latte->addProvider('uiPresenter', $this->getPresenter());
		$latte->addProvider('uiControl', $this);
		$latte->addProvider('snippetBridge', new Nette\Bridges\ApplicationLatte\SnippetBridge($this));
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
		UIMacros::install($latte->getCompiler());
		$latte->addProvider('uiControl', $this);
		$latte->addProvider('snippetBridge', new Nette\Bridges\ApplicationLatte\SnippetBridge($this));
		$latte->render('{snippet foo}{control test}{/snippet}');
	}
}


$presenter = new TestPresenter;
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
$presenter->snippetMode = true;
$presenter['test']->redrawControl('foo');
$presenter->render();
Assert::same([
	'snippets' => [
		'snippet-test-foo' => 'hello',
	],
], (array) $presenter->payload);
