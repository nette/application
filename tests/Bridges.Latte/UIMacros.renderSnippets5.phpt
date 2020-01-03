<?php

/**
 * Test: UIMacros, n:snippet
 */

declare(strict_types=1);

use Nette\Bridges\ApplicationLatte\SnippetBridge;
use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';



class TestPresenter extends Nette\Application\UI\Presenter
{
	public function render(string $template)
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		UIMacros::install($latte->getCompiler());
		$latte->addProvider('uiControl', $this);
		$latte->addProvider('snippetBridge', new SnippetBridge($this));
		$latte->render($template, ['presenter' => $this]);
	}
}


$presenter = new TestPresenter;
$presenter->snippetMode = true;
$presenter->redrawControl('foo');
$presenter->render('<div n:snippet=foo>Hello</div>');
Assert::same([
	'snippets' => [
		'snippet--foo' => 'Hello',
	],
], (array) $presenter->payload);


$presenter = new TestPresenter;
$presenter->snippetMode = true;
$presenter->redrawControl('foo');
Assert::exception(function () use ($presenter) {
	$presenter->render('<div id="x" n:snippet=foo>Hello</div>');
}, Latte\CompileException::class, 'Cannot combine HTML attribute id with n:snippet.');


$presenter = new TestPresenter;
$presenter->snippetMode = true;
$presenter->redrawControl('foo');
Assert::exception(function () use ($presenter) {
	$presenter->render('<div n:snippet="$foo">Hello</div>');
}, Latte\CompileException::class, 'Dynamic snippets are allowed only inside static snippet/snippetArea.');


$presenter = new TestPresenter;
$presenter->snippetMode = true;
$presenter->redrawControl('foo');
Assert::exception(function () use ($presenter) {
	$presenter->render('<div n:snippet="foo"><div id="x" n:snippet="$foo">Hello</div></div>');
}, Latte\CompileException::class, 'Cannot combine HTML attribute id with n:snippet.');
