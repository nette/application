<?php

/**
 * Test: {control ...}
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}

$control = new class {
	public function render()
	{
		echo '<>&amp;';
	}


	public function __call($name, $args)
	{
		return new self;
	}
};

$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(null));
$latte->addProvider('uiControl', $control);


Assert::exception(
	fn() => $latte->renderToString('<div {control x}>'),
	Latte\RuntimeException::class,
	'Filters: unable to convert content type HTML to HTML/TAG',
);

Assert::same(
	'<div <>&amp;>',
	$latte->renderToString('<div {control x|noescape}>'),
);

Assert::same(
	'<div title="&lt;&gt;&amp;">',
	$latte->renderToString('<div title="{control x}">'),
);

Assert::exception(
	fn() => $latte->renderToString('<style> {control x} </style>'),
	Latte\RuntimeException::class,
	'Filters: unable to convert content type HTML to HTML/%a?%CSS',
);
