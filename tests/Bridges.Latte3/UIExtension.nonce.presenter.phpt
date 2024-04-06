<?php

/**
 * Test: UIExtension nonce
 */

declare(strict_types=1);

use Nette\Application\UI;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}

Tester\Environment::bypassFinals();

$response = Mockery::mock(Nette\Http\IResponse::class);
$response->shouldReceive('getHeader')->with('Content-Security-Policy')->andReturn("hello 'nonce-abcd123==' world");

$presenter = Mockery::mock(UI\Presenter::class);
$presenter->shouldReceive('getPresenterIfExists')->andReturn($presenter);
$presenter->shouldReceive('getHttpResponse')->andReturn($response);
$presenter->shouldIgnoreMissing();

$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($presenter));

Assert::match(
	'<script nonce="abcd123=="></script>',
	$latte->renderToString('<script n:nonce></script>'),
);
