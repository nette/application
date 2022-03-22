<?php

/**
 * Test: TemplateFactory nonce
 * @phpVersion 8.0
 */

declare(strict_types=1);

use Nette\Application\UI;
use Nette\Bridges\ApplicationLatte;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}

Tester\Environment::bypassFinals();

$latte = new Latte\Engine;

$latteFactory = Mockery::mock(ApplicationLatte\LatteFactory::class);
$latteFactory->shouldReceive('create')->andReturn($latte);

$response = Mockery::mock(Nette\Http\IResponse::class);
$response->shouldReceive('getHeader')->with('Content-Security-Policy')->andReturn("hello 'nonce-abcd123==' world");

$presenter = Mockery::mock(UI\Presenter::class);
$presenter->shouldReceive('getPresenterIfExists')->andReturn($presenter);
$presenter->shouldReceive('getHttpResponse')->andReturn($response);
$presenter->shouldIgnoreMissing();

$factory = new ApplicationLatte\TemplateFactory($latteFactory);
$factory->createTemplate($presenter);

$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::match(
	'<script nonce="abcd123=="></script>',
	$latte->renderToString('<script n:nonce></script>'),
);
