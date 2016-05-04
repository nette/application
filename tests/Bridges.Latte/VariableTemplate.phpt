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
