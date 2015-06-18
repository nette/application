<?php

/**
 * Test: snippets.
 */

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MockControl
{
	function __call($name, $args)
	{
	}
}


$latte = new Latte\Engine;
UIMacros::install($latte->getCompiler());
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::match(<<<EOD
<div>
<div id="">	hello
</div></div>
EOD
, $latte->renderToString(<<<EOD
<div>
	{snippet abc}
	hello
	{/snippet}
</div>
EOD
, ['_control' => new MockControl]));
