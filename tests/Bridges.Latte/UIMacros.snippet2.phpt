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
<p><div id="">hello</div> world</p>
EOD
, $latte->renderToString(<<<EOD
<p>{snippet abc}hello{/snippet} world</p>
EOD
, ['_control' => new MockControl]));
