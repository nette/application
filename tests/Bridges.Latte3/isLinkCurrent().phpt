<?php

/**
 * Test: isLinkCurrent()
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}

Tester\Environment::bypassFinals();

$presenter = Mockery::mock(Nette\Application\UI\Presenter::class);
$presenter->shouldReceive('getPresenterIfExists')->andReturn($presenter);
$presenter->shouldReceive('getHttpResponse')->andReturn((Mockery::mock(Nette\Http\IResponse::class))->shouldIgnoreMissing());
$presenter->shouldIgnoreMissing();

$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension($presenter));

Assert::matchFile(
	__DIR__ . '/expected/isLinkCurrent.php',
	$latte->compile(
		<<<'XX'
			<a n:href="default" n:class="$presenter->isLinkCurrent() ? current">n:href before n:class</a>

			<a n:class="$presenter->isLinkCurrent() ? current" n:href="default">n:href after n:class</a>

			<a href="{link default}" n:class="$presenter->isLinkCurrent() ? current">href before n:class</a>

			<a n:class="$presenter->isLinkCurrent() ? current" href="{link default}">href after n:class</a>

			<a n:class="isLinkCurrent('default') ? current" n:href="default">custom function</a>

			XX,
	),
);
