<?php

/**
 * Test: snippets.
 */

use Nette\Bridges\ApplicationLatte\UIMacros,
	Tester\Assert;


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

Assert::match('<div id=""><em>test</em></div>'
, $latte->renderToString('{snippetArea defaults}
{if isset($template->testTpl) === FALSE}
    {?$template->testTpl = \'<em>{:test}</em>\'}
{/if}
{/snippetArea}
{snippet test}{=strtr($testTpl, array(\'{:test}\' => $test))|noescape}{/snippet}'
, array('_control' => new MockControl,'test' => 'test')));
