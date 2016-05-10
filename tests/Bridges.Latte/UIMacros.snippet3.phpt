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
$params['_control'] = new MockControl;

Assert::match(<<<EOD
<p id="">hello</p>
EOD
, $latte->renderToString(<<<EOD
<p n:inner-snippet="abc">hello</p>
EOD
, $params));


Assert::match(<<<EOD
<p id="">hello</p>
EOD
, $latte->renderToString(<<<EOD
<p n:snippet="abc">hello</p>
EOD
, $params));


Assert::error(function () use ($latte) {
	$latte->compile('<p n:snippet="abc" n:foreach="$items as $item">hello</p>');
}, E_USER_WARNING, 'Combination of n:snippet with n:foreach is invalid, use n:inner-foreach.');
