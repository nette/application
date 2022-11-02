<?php

/** @phpVersion 8.0 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}

$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(null));


Assert::exception(
	fn() => $latte->compile('<p n:snippet="abc" n:foreach="$items as $item">hello</p>'),
	Latte\CompileException::class,
	'Combination of n:snippet with n:foreach is invalid, use n:inner-foreach (on line 1 at column 4)',
);

Assert::exception(
	fn() => $latte->compile('<p n:snippet="abc" id="a">hello</p>'),
	Latte\CompileException::class,
	'Cannot combine HTML attribute id with n:snippet (on line 1 at column 4)',
);

Assert::exception(
	fn() => $latte->compile('<p n:snippet="abc" n:ifcontent>hello</p>'),
	Latte\CompileException::class,
	'Cannot combine n:ifcontent with n:snippet (on line 1 at column 4)',
);

Assert::exception(
	fn() => $latte->compile('<div n:inner-snippet="inner"></div>'),
	Latte\CompileException::class,
	'Use n:snippet instead of n:inner-snippet (on line 1 at column 6)',
);

Assert::exception(
	fn() => $latte->renderToString('{snippet foo} {include parent} {/snippet}'),
	Latte\CompileException::class,
	'Cannot include parent block outside of any block (on line 1 at column 15)',
);

Assert::exception(
	fn() => $latte->renderToString('{snippet foo} {include this} {/snippet}'),
	Latte\CompileException::class,
	'Cannot include this block outside of any block (on line 1 at column 15)',
);
