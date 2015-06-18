<?php

/**
 * Test: dynamic snippets test.
 */

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
UIMacros::install($latte->getCompiler());

Assert::matchFile(
	__DIR__ . '/expected/UIMacros.dynamicsnippets.alt.phtml',
	$latte->compile(__DIR__ . '/templates/dynamicsnippets.alt.latte')
);
