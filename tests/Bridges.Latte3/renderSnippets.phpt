<?php

/**
 * Test: render snippets.
 */

declare(strict_types=1);

use Nette\Http;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


class InnerControl extends Nette\Application\UI\Control
{
	public function render()
	{
		$latte = new Latte\Engine;
		$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($this));
		$params['say'] = 'Hello';
		$latte->render(__DIR__ . '/templates/snippet-included.latte', $params);
	}
}

class TestPresenter extends Nette\Application\UI\Presenter
{
	public function createComponentMulti()
	{
		return new Nette\Application\UI\Multiplier(function () {
			$control = new InnerControl;
			$control->redrawControl();
			return $control;
		});
	}


	public function render()
	{
		$latte = new Latte\Engine;
		$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($this));
		$latte->render(__DIR__ . '/templates/snippet-include.latte');
	}
}


$presenter = new TestPresenter;
$presenter->injectPrimary(new Http\Request(new Http\UrlScript('/')), new Http\Response);
$presenter->snippetMode = true;
$presenter->redrawControl();
$presenter['multi-1']->redrawControl();
$presenter->render();
Assert::same([
	'snippets' => [
		'snippet--hello' => 'Hello',
		'snippet--include' => "\n<p>Included file #3 (A, B)</p>\n",
		'snippet--array-1' => 'Value 1',
		'snippet--array-2' => 'Value 2',
		'snippet--array-3' => 'Value 3',
		'snippet--array2-1' => 'Value 1',
		'snippet--array2-2' => 'Value 2',
		'snippet--array2-3' => 'Value 3',
		'snippet--includeSay' => 'Hello include snippet',
		'snippet--nested1' => "\n	<div id=\"snippet--nested2\">Foo</div>\n",
		'snippet-multi-1-includeSay' => 'Hello',
	],
], (array) $presenter->payload);



$presenter = new TestPresenter;
$presenter->injectPrimary(new Http\Request(new Http\UrlScript('/')), new Http\Response);
$presenter->snippetMode = true;
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
$presenter->injectPrimary(new Http\Request(new Http\UrlScript('/')), new Http\Response);
ob_start();
$presenter->render();
$content = ob_get_clean();
Assert::matchFile(__DIR__ . '/expected/renderSnippets.html', $content);
