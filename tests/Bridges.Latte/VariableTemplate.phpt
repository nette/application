<?php

/**
 * VariableTemplate
 */

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Nette\Application\UI\Presenter
{

}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader());
UIMacros::install($latte->getCompiler());
Assert::match('3', $latte->renderToString('{$template->length($bar)}', ['bar' => 'aaa', '_control' => new TestPresenter()]));


$latte = new Latte\Engine;
UIMacros::install($latte->getCompiler());
$result = $latte->renderToString(__DIR__ . '/templates/variableTemplate.child.latte', ['_control' => new TestPresenter()]);
Assert::match('1
<div id="snippet--snippet">2</div>34
<div id="snippet--snippet2">5
</div>', $result);


$presenter = new TestPresenter;
$presenter->snippetMode = TRUE;
$presenter->redrawControl();
$latte = new Latte\Engine;
UIMacros::install($latte->getCompiler());
$result = $latte->renderToString(__DIR__ . '/templates/variableTemplate.child.latte', ['_control' => $presenter]);
Assert::same([
	'snippets' => [
		'snippet--snippet' => '2',
		'snippet--snippet2' => "5\n",
	],
], (array) $presenter->payload);
