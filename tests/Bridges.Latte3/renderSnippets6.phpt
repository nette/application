<?php

/**
 * Test: n:snippet and custom HTML attribute.
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
		Nette\Bridges\ApplicationLatte\Nodes\SnippetNode::$snippetAttribute = 'data-snippet';
		$latte->render($template);
	}
}


$presenter = new TestPresenter;
$presenter->injectPrimary(new Http\Request(new Http\UrlScript('/')), new Http\Response);
ob_start();
$presenter->render('<div n:snippet=test>hello</div>');
$content = ob_get_clean();
Assert::same('<div data-snippet="snippet--test">hello</div>', $content);


$presenter = new TestPresenter;
$presenter->injectPrimary(new Http\Request(new Http\UrlScript('/')), new Http\Response);
Assert::exception(
	fn() => $presenter->render('<div n:snippet=test data-snippet>hello</div>'),
	Latte\CompileException::class,
	'Cannot combine HTML attribute data-snippet with n:snippet (on line 1 at column 6)',
);
