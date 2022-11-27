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
	<div n:snippet="snippet" n:block="block1">
			static
	</div>


	{snippet outer}
	begin
	<div n:snippet="inner-{$id}" n:block="block2">
			dynamic
	</div>
	end
	{/snippet}
	EOD;

Assert::matchFile(
	__DIR__ . '/expected/n-snippet.block.php',
	$latte->compile($template),
);
