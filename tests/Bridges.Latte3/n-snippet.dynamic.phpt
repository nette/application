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
		{snippet outer1}
		{foreach array(1,2,3) as $id}
			<div n:snippet="inner-{$id}">
					#{$id}
			</div>
		{/foreach}
		{/snippet}
	EOD;

Assert::matchFile(
	__DIR__ . '/expected/n-snippet.dynamic.php',
	$latte->compile($template),
);
