<?php

/**
 * Test: UIMacros, renderSnippets and template with layout
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
		return $latte->renderToString(__DIR__ . '/templates/snippets.extends.latte', $params);
	}
}


$presenter = new TestPresenter;
$presenter->snippetMode = TRUE;
$presenter->redrawControl('foo');
$content = $presenter->render();
Assert::same('', $content);
Assert::same([
	'snippets' => [
		'snippet--foo' => 'Hello',
	],
], (array) $presenter->payload);
