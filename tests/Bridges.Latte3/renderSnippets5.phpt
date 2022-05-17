<?php

/**
 * Test: n:snippet
 */

declare(strict_types=1);

use Nette\Http;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


class TestPresenter extends Nette\Application\UI\Presenter
{
	public function render(string $template)
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($this));
		$latte->render($template, ['presenter' => $this]);
	}
}


$presenter = new TestPresenter;
$presenter->injectPrimary(null, null, null, new Http\Request(new Http\UrlScript('/')), new Http\Response);
$presenter->snippetMode = true;
$presenter->redrawControl('foo');
$presenter->render('<div n:snippet=foo>Hello</div>');
Assert::same([
	'snippets' => [
		'snippet--foo' => 'Hello',
	],
], (array) $presenter->payload);


$presenter = new TestPresenter;
$presenter->injectPrimary(null, null, null, new Http\Request(new Http\UrlScript('/')), new Http\Response);
$presenter->snippetMode = true;
$presenter->redrawControl('foo');
Assert::exception(
	fn() => $presenter->render('<div id="x" n:snippet=foo>Hello</div>'),
	Latte\CompileException::class,
	'Cannot combine HTML attribute id with n:snippet (on line 1 at column 13)',
);



$presenter = new TestPresenter;
$presenter->injectPrimary(null, null, null, new Http\Request(new Http\UrlScript('/')), new Http\Response);
$presenter->snippetMode = true;
$presenter->redrawControl('foo');
Assert::exception(
	fn() => $presenter->render('<div n:snippet="foo"><div id="x" n:snippet="$foo">Hello</div></div>'),
	Latte\CompileException::class,
	'Cannot combine HTML attribute id with n:snippet (on line 1 at column 34)',
);
