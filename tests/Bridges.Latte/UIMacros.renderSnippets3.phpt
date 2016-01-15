<?php

/**
 * Test: UIMacros, renderSnippets and dynamic snippetArea with included template
 */

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

class TestPresenter extends Nette\Application\UI\Presenter
{

	public function render()
	{
		$latte = new Latte\Engine;
		UIMacros::install($latte->getCompiler());
		$params['_control'] = $this;
		$latte->setTempDirectory(__DIR__ . '/../tmp/');
		$latte->render(__DIR__ . '/templates/snippetArea-include.latte', $params);
	}
}


$presenter = new TestPresenter;
$presenter->snippetMode = TRUE;
$presenter->redrawControl('foo');
$presenter->redrawControl('data');
$presenter->render();
Assert::same([
	'snippets' => [
		'snippet--bar-1' => "1\n",
		'snippet--bar-2' => "2\n",
	],
], (array) $presenter->payload);
