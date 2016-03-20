<?php

/**
 * Test: UIMacros and renderSnippets with blocks included using includeblock
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
		$latte->render(__DIR__ . '/templates/snippets.includeblock.latte', $params);
	}
}


$presenter = new TestPresenter;
$presenter->snippetMode = TRUE;
$presenter->redrawControl();
$presenter->render();
Assert::same([
	'snippets' => [
		'snippet--test' => 'bar',
	],
], (array) $presenter->payload);
