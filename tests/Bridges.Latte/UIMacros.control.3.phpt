<?php

/**
 * Test: {control ...}
 */

declare(strict_types=1);

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
UIMacros::install($latte->getCompiler());
$latte->addProvider('uiControl', new class {
	public function __call($name, $args)
	{
		return new self;
	}
});

Assert::error(function () use ($latte) {
	$latte->renderToString('<div {control x}');
}, E_USER_WARNING, 'Tag {control} must be used in HTML text.');

Assert::error(function () use ($latte) {
	$latte->renderToString('<div title="{control x}"');
}, E_USER_WARNING, 'Tag {control} must be used in HTML text.');

Assert::error(function () use ($latte) {
	$latte->renderToString('<style> {control x} </style>');
}, E_USER_WARNING, 'Tag {control} must be used in HTML text.');
