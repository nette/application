<?php

/**
 * Test: ComponentReflection::combineArgs()
 * @phpVersion 8.0
 */

declare(strict_types=1);

use Nette\Application\UI\ComponentReflection as Reflection;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class MyPresenter
{
	public function hintsUnion(int|array $intArray, string|array $strArray)
	{
	}
}


test('', function () {
	$method = new ReflectionMethod('MyPresenter', 'hintsUnion');

	Assert::same([1, 'abc'], Reflection::combineArgs($method, ['intArray' => '1', 'strArray' => 'abc']));
	Assert::same([[1], [2]], Reflection::combineArgs($method, ['intArray' => [1], 'strArray' => [2]]));

	Assert::exception(
		fn() => Reflection::combineArgs($method, []),
		Nette\InvalidArgumentException::class,
		'Missing parameter $intArray required by MyPresenter::hintsUnion()'
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['intArray' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $intArray passed to MyPresenter::hintsUnion() must be array|int, string given.'
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['intArray' => null]),
		Nette\InvalidArgumentException::class,
		'Missing parameter $intArray required by MyPresenter::hintsUnion()'
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['intArray' => new stdClass]),
		Nette\InvalidArgumentException::class,
		'Argument $intArray passed to MyPresenter::hintsUnion() must be array|int, stdClass given.'
	);
});
