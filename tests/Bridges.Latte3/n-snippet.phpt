<?php

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}

class Test
{
	public const Foo = 'hello';
}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(null));

$template = <<<'EOD'
		<div class="test" n:snippet="outer">
		<p>Outer</p>
		</div>

		<div n:snippet="gallery" class="{=class}"></div>

		<script n:snippet="script">{='x'}</script>

		<script n:snippet="Test::Foo">{='y'}</script>

	EOD;

Assert::matchFile(
	__DIR__ . '/expected/n-snippet.php',
	$latte->compile($template),
);
