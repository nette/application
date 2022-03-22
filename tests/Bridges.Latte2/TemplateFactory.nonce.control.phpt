<?php

/**
 * Test: TemplateFactory nonce
 */

declare(strict_types=1);

use Nette\Application\UI;
use Nette\Bridges\ApplicationLatte;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

$latte = new Latte\Engine;

$latteFactory = Mockery::mock(ApplicationLatte\LatteFactory::class);
$latteFactory->shouldReceive('create')->andReturn($latte);

$response = Mockery::mock(Nette\Http\IResponse::class);
$response->shouldReceive('getHeader')->with('Content-Security-Policy')->andReturn("hello 'nonce-abcd123==' world");

$control = Mockery::mock(UI\Control::class);
$control->shouldReceive('getPresenter')->andReturn(null);
$control->shouldIgnoreMissing();

$factory = new ApplicationLatte\TemplateFactory($latteFactory);
$factory->createTemplate($control);

$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::match(
	'<script></script>',
	$latte->renderToString('<script n:nonce></script>')
);
