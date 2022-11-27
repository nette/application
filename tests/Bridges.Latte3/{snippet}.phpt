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
		{snippet}

		{/snippet}



		{snippet outer}
		Outer
			{snippet inner}Inner{/snippet inner}
		/Outer
		{/snippet outer}



		@{if true} Hello World @{/if}

		{snippet title}Title 1{/snippet title}

		{snippet title2}Title 2{/snippet}
	EOD;

Assert::matchFile(
	__DIR__ . '/expected/snippet.php',
	$latte->compile($template),
);
