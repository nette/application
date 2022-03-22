<?php

/**
 * Test: renderSnippets and control with two templates.
 * @phpVersion 8.0
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
		$this->renderA();
		$this->renderB();
	}


	public function renderA()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($this));
		$params['say'] = 'Hello';
		$latte->render('{snippet testA}{$say}{/snippet}', $params);
	}


	public function renderB()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($this));
		$params['say'] = 'world';
		$latte->render('{snippet testB}{$say}{/snippet}', $params);
	}
}

class TestPresenter extends Nette\Application\UI\Presenter
{
	public function createComponentMulti()
	{
		return new Nette\Application\UI\Multiplier(fn() => new InnerControl);
	}


	public function render()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($this));
		$latte->render('');
	}
}


$presenter = new TestPresenter;
$presenter->injectPrimary(null, null, null, new Http\Request(new Http\UrlScript('/')), new Http\Response);
$presenter->snippetMode = true;
$presenter['multi-1']->redrawControl();
$presenter->render();
Assert::same([
	'snippets' => [
		'snippet-multi-1-testA' => 'Hello',
		'snippet-multi-1-testB' => 'world',
	],
], (array) $presenter->payload);
