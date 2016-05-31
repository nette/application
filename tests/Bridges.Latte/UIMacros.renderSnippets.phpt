<?php

/**
 * Test: UIMacros and renderSnippets.
 */

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class InnerControl extends Nette\Application\UI\Control
{
	public function render()
	{
		$latte = new Latte\Engine;
		UIMacros::install($latte->getCompiler());
		$latte->addProvider('uiPresenter', $this->getPresenter());
		$latte->addProvider('uiControl', $this);
		$latte->addProvider('snippetBridge', new Nette\Bridges\ApplicationLatte\SnippetBridge($this));
		$params['say'] = 'Hello';
		$latte->render(__DIR__ . '/templates/snippet-included.latte', $params);
	}
}

class TestPresenter extends Nette\Application\UI\Presenter
{
	function createComponentMulti()
	{
		return new Nette\Application\UI\Multiplier(function () {
			$control = new InnerControl();
			$control->redrawControl();
			return $control;
		});
	}

	public function render()
	{
		$latte = new Latte\Engine;
		UIMacros::install($latte->getCompiler());
		$latte->addProvider('snippetBridge', new Nette\Bridges\ApplicationLatte\SnippetBridge($this));
		$latte->render(__DIR__ . '/templates/snippet-include.latte');
	}
}


$presenter = new TestPresenter;
$presenter->snippetMode = TRUE;
$presenter->redrawControl();
$presenter['multi-1']->redrawControl();
$presenter->render();
Assert::same([
	'snippets' => [
		'snippet--hello' => 'Hello',
		'snippet--include' => "<p>Included file #3 (A, B)</p>\n",
		'snippet--array-1' => 'Value 1',
		'snippet--array-2' => 'Value 2',
		'snippet--array-3' => 'Value 3',
		'snippet--array2-1' => 'Value 1',
		'snippet--array2-2' => 'Value 2',
		'snippet--array2-3' => 'Value 3',
		'snippet--includeSay' => 'Hello include snippet',
		'snippet--nested1' => "\t<div id=\"snippet--nested2\">Foo</div>",
		'snippet-multi-1-includeSay' => 'Hello',
	],
], (array) $presenter->payload);



$presenter = new TestPresenter;
$presenter->snippetMode = TRUE;
$presenter->redrawControl('hello');
$presenter->redrawControl('array');
$presenter->render();

Assert::same([
	'snippets' => [
		'snippet--hello' => 'Hello',
		'snippet--array-1' => 'Value 1',
		'snippet--array-2' => 'Value 2',
		'snippet--array-3' => 'Value 3',
	],
], (array) $presenter->payload);

$presenter = new TestPresenter;
ob_start();
$presenter->render();
$content = ob_get_clean();
Assert::matchFile(__DIR__ .'/expected/UIMacros.renderSnippets.html', $content);
