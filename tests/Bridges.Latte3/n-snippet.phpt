<?php

/** @phpVersion 8.0 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(null));

$template = <<<'EOD'
		<div class="test" n:snippet="outer">
		<p>Outer</p>
		</div>

		<div n:snippet="gallery" class="{=class}"></div>

	EOD;

Assert::matchFile(
	__DIR__ . '/expected/n-snippet.phtml',
	$latte->compile($template),
);
