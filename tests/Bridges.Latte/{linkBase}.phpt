<?php

/**
 * Test: {linkBase ...}
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(null));


Assert::contains(
	'$this->global->uiControl->link(\'foo\')',
	$latte->compile('{linkBase Base}{link foo}'),
);
Assert::contains(
	'$this->global->uiControl->link(\':Base:Foo:\')',
	$latte->compile('{linkBase Base}{link Foo:}'),
);
Assert::contains(
	'$this->global->uiControl->link(\':Foo:\')',
	$latte->compile('{linkBase Base}{link :Foo:}'),
);


// dynamic
Assert::contains(
	'$this->global->uiControl->link(Nette\Application\LinkGenerator::applyBase($link, \'Base\'))',
	$latte->compile('{linkBase Base}{link $link}'),
);
Assert::contains(
	'$this->global->uiControl->link(Nette\Application\LinkGenerator::applyBase(\'foo\', $base))',
	$latte->compile('{linkBase $base}{link foo}'),
);
