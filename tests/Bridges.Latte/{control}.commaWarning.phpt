<?php declare(strict_types=1);

/**
 * Test: {control ...} deprecation for a missing comma before arguments
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(null));


test('no warning when only the component name is given', function () use ($latte) {
	Assert::noError(fn() => $latte->compile('{control form}'));
});


test('no warning when comma separates name and arguments', function () use ($latte) {
	Assert::noError(fn() => $latte->compile('{control form, param => 1}'));
});


test('deprecation warning when comma is missing before arguments', function () use ($latte) {
	Assert::error(
		fn() => $latte->compile('{control form param => 1}'),
		E_USER_DEPRECATED,
		'Missing comma before tag arguments on line 1 at column 15.',
	);
});


test('deprecation warning also fires after the :method syntax', function () use ($latte) {
	Assert::error(
		fn() => $latte->compile('{control form:type param}'),
		E_USER_DEPRECATED,
		'Missing comma before tag arguments on line 1 at column 20.',
	);
});


test('a malformed tag emits the deprecation together with the exception', function () use ($latte) {
	Assert::error(function () use ($latte) {
		Assert::exception(
			fn() => $latte->compile('{control form: param => 1}'),
			Latte\CompileException::class,
			"Unexpected '=>' (on line 1 at column 22)",
		);
	}, E_USER_DEPRECATED, 'Missing comma before tag arguments on line 1 at column 22.');
});
