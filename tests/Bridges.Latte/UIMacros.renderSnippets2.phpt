<?php

/**
 * Test: UIMacros, renderSnippets and control with two templates.
 */

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class InnerControl extends Nette\Application\UI\Control
{
	public function render()
	{
		$this->renderA();
		$this->renderB();
	}

	public function renderA()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		UIMacros::install($latte->getCompiler());
		$latte->addProvider('uiPresenter', $this->getPresenter());
		$latte->addProvider('uiControl', $this);
		$latte->addProvider('snippetBridge', new Nette\Bridges\ApplicationLatte\SnippetBridge($this));
		$params['say'] = 'Hello';
		$latte->render('{snippet testA}{$say}{/snippet}', $params);
	}

	public function renderB()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		UIMacros::install($latte->getCompiler());
		$latte->addProvider('uiPresenter', $this->getPresenter());
		$latte->addProvider('uiControl', $this);
		$latte->addProvider('snippetBridge', new Nette\Bridges\ApplicationLatte\SnippetBridge($this));
		$params['say'] = 'world';
		$latte->render('{snippet testB}{$say}{/snippet}', $params);
	}

}

class TestPresenter extends Nette\Application\UI\Presenter
{
	function createComponentMulti()
	{
		return new Nette\Application\UI\Multiplier(function () {
			return new InnerControl();
		});
	}

	public function render()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		UIMacros::install($latte->getCompiler());
		$latte->addProvider('uiControl', $this);
		$latte->addProvider('snippetBridge', new Nette\Bridges\ApplicationLatte\SnippetBridge($this));
		$latte->render('');
	}
}


$presenter = new TestPresenter;
$presenter->snippetMode = TRUE;
$presenter['multi-1']->redrawControl();
$presenter->render();
Assert::same([
	'snippets' => [
		'snippet-multi-1-testA' => 'Hello',
		'snippet-multi-1-testB' => 'world',
	],
], (array) $presenter->payload);
