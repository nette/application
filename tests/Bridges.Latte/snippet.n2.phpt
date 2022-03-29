<?php

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/mocks/SnippetBridgeMock.php';

$bridge = new SnippetBridgeMock;
$bridge->snippetMode = false;


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension);
$latte->addProvider('snippetBridge', $bridge);

Assert::match(<<<'EOD'
	<p id="abc">hello</p>
	EOD
, $latte->renderToString(
	<<<'EOD'
		<p n:snippet="abc">hello</p>
		EOD,
));


Assert::exception(
	fn() => $latte->compile('<p n:snippet="abc" n:foreach="$items as $item">hello</p>'),
	Latte\CompileException::class,
	'Combination of n:snippet with n:foreach is invalid, use n:inner-foreach.',
);

Assert::exception(
	fn() => $latte->compile('<p n:snippet="abc" id="a">hello</p>'),
	Latte\CompileException::class,
	'Cannot combine HTML attribute id with n:snippet.',
);

Assert::exception(
	fn() => $latte->compile('<p n:snippet="abc" n:ifcontent>hello</p>'),
	Latte\CompileException::class,
	'Cannot combine n:ifcontent with n:snippet.',
);

Assert::exception(
	fn() => $latte->compile('<div n:inner-snippet="inner"></div>'),
	Latte\CompileException::class,
	'Use n:snippet instead of n:inner-snippet',
);

Assert::exception(
	fn() => $latte->renderToString('{snippet foo} {include parent} {/snippet}'),
	Latte\CompileException::class,
	'Cannot include parent block outside of any block.',
);

Assert::exception(
	fn() => $latte->renderToString('{snippet foo} {include this} {/snippet}'),
	Latte\CompileException::class,
	'Cannot include this block outside of any block.',
);
