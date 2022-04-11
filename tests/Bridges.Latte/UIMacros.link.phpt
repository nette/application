<?php

/**
 * Test: UIMacros: {link ...}
 */

declare(strict_types=1);

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
UIMacros::install($latte->getCompiler());

// {link ...}
Assert::contains(
	'$this->global->uiControl->link("p")',
	$latte->compile('{link p}')
);
Assert::contains(
	'($this->filters->filter)($this->global->uiControl->link("p"))',
	$latte->compile('{link p|filter}')
);
Assert::contains(
	'$this->global->uiControl->link("p:a")',
	$latte->compile('{link p:a}')
);
Assert::contains(
	'$this->global->uiControl->link($dest)',
	$latte->compile('{link $dest}')
);
Assert::contains(
	'$this->global->uiControl->link($p:$a)',
	$latte->compile('{link $p:$a}')
);
Assert::contains(
	'$this->global->uiControl->link("$p:$a")',
	$latte->compile('{link "$p:$a"}')
);
Assert::contains(
	'$this->global->uiControl->link("p:a")',
	$latte->compile('{link "p:a"}')
);
Assert::contains(
	'$this->global->uiControl->link(\'p:a\')',
	$latte->compile('{link \'p:a\'}')
);

Assert::contains(
	'$this->global->uiControl->link("p", [\'param\'])',
	$latte->compile('{link p param}')
);
Assert::contains(
	'$this->global->uiControl->link("p", [\'param\' => 123])',
	$latte->compile('{link p param => 123}')
);
Assert::contains(
	'$this->global->uiControl->link("p", [\'param\' => 123])',
	$latte->compile('{link p, param => 123}')
);
