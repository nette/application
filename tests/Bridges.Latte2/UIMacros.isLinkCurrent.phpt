<?php

/**
 * Test: isLinkCurrent()
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '>')) {
	Tester\Environment::skip('Test for Latte 2');
}

Tester\Environment::bypassFinals();

$latte = new Latte\Engine;

$latteFactory = Mockery::mock(Nette\Bridges\ApplicationLatte\LatteFactory::class);
$latteFactory->shouldReceive('create')->andReturn($latte);

$presenter = Mockery::mock(Nette\Application\UI\Presenter::class);
$presenter->shouldReceive('getPresenterIfExists')->andReturn($presenter);
$presenter->shouldReceive('getHttpResponse')->andReturn((Mockery::mock(Nette\Http\IResponse::class))->shouldIgnoreMissing());
$presenter->shouldIgnoreMissing();

$factory = new Nette\Bridges\ApplicationLatte\TemplateFactory($latteFactory);
$factory->createTemplate($presenter);

$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::matchFile(
	__DIR__ . '/expected/UIMacros.isLinkCurrent.php',
	$latte->compile(
		<<<'XX'
<a n:href="default" n:class="$presenter->isLinkCurrent() ? current">n:href before n:class</a>

<a n:class="$presenter->isLinkCurrent() ? current" n:href="default">n:href after n:class</a>

<a href="{link default}" n:class="$presenter->isLinkCurrent() ? current">href before n:class</a>

<a n:class="$presenter->isLinkCurrent() ? current" href="{link default}">href after n:class</a>

{ifCurrent}empty{/ifCurrent}

{ifCurrent default}default{/ifCurrent}

<a n:class="isLinkCurrent(default) ? current" n:href="default">custom function</a>

XX
	)
);
