<?php

/**
 * Test: ComponentReflection::combineArgs()
 */

declare(strict_types=1);

use Nette\Application\UI\ComponentReflection as Reflection;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class MyPresenter
{
	public function params($int, $bool, $str, $arr)
	{
	}


	public function hints(int $int, bool $bool, string $str, array $arr, iterable $iter)
	{
	}


	public function hintsNulls(
		?int $int = null,
		?bool $bool = null,
		?string $str = null,
		?array $arr = null,
		?iterable $iter = null,
	) {
	}


	public function hintsNullable(?int $int, ?bool $bool, ?string $str, ?array $arr, ?iterable $iter)
	{
	}


	public function hintsDefaults(
		int $int = 0,
		bool $bool = false,
		string $str = '',
		array $arr = [],
		iterable $iter = [],
	) {
	}


	public function defaults($int = 0, $bool = false, $str = '', $arr = [])
	{
	}


	public function objects(stdClass $req, ?stdClass $nullable, ?stdClass $opt = null)
	{
	}


	public function hintsUnion(int|array $intArray, string|array $strArray)
	{
	}
}


test('', function () {
	$method = new ReflectionMethod('MyPresenter', 'params');

	Assert::same([null, null, null, null], Reflection::combineArgs($method, []));
	Assert::same([null, null, null, null], Reflection::combineArgs($method, ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
	Assert::same([1, true, 'abc', '1'], Reflection::combineArgs($method, ['int' => 1, 'bool' => true, 'str' => 'abc', 'arr' => '1']));
	Assert::same([0, false, '', ''], Reflection::combineArgs($method, ['int' => 0, 'bool' => false, 'str' => '', 'arr' => '']));
	Assert::equal([null, null, null, new stdClass], Reflection::combineArgs($method, ['arr' => new stdClass]));

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => []]),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::params() must be scalar, array given.',
	);
});


test('', function () {
	$method = new ReflectionMethod('MyPresenter', 'hints');

	Assert::same([1, true, 'abc', [1], [2]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1], 'iter' => [2]]));
	Assert::same([0, false, '', [], []], Reflection::combineArgs($method, ['int' => 0, 'bool' => false, 'str' => ''])); // missing 'arr', 'iter'

	Assert::exception(
		fn() => Reflection::combineArgs($method, []),
		Nette\InvalidArgumentException::class,
		'Missing parameter $int required by MyPresenter::hints()',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::hints() must be int, string given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => null]),
		Nette\InvalidArgumentException::class,
		'Missing parameter $int required by MyPresenter::hints()',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => new stdClass]),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::hints() must be int, stdClass given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => []]),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::hints() must be int, array given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '1', 'bool' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $bool passed to MyPresenter::hints() must be bool, string given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $arr passed to MyPresenter::hints() must be array, string given.',
	);
});


test('', function () {
	$method = new ReflectionMethod('MyPresenter', 'hintsNulls');

	Assert::same([null, null, null, null, null], Reflection::combineArgs($method, []));
	Assert::same([null, null, null, null, null], Reflection::combineArgs($method, ['int' => null, 'bool' => null, 'str' => null, 'arr' => null, 'iter' => null]));
	Assert::same([1, true, 'abc', [1], [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1], 'iter' => [1]]));
	Assert::same([0, false, '', [], []], Reflection::combineArgs($method, ['int' => 0, 'bool' => false, 'str' => '', 'arr' => [], 'iter' => []]));

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::hintsNulls() must be int, string given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => new stdClass]),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::hintsNulls() must be int, stdClass given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => []]),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::hintsNulls() must be int, array given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '1', 'bool' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $bool passed to MyPresenter::hintsNulls() must be bool, string given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $arr passed to MyPresenter::hintsNulls() must be array, string given.',
	);
});


test('', function () {
	$method = new ReflectionMethod('MyPresenter', 'hintsNullable');

	Assert::same([null, null, null, null, null], Reflection::combineArgs($method, []));
	Assert::same([null, null, null, null, null], Reflection::combineArgs($method, ['int' => null, 'bool' => null, 'str' => null, 'arr' => null, 'iter' => null]));
	Assert::same([1, true, 'abc', [1], [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1], 'iter' => [1]]));
	Assert::same([0, false, '', [], []], Reflection::combineArgs($method, ['int' => 0, 'bool' => false, 'str' => '', 'arr' => [], 'iter' => []]));

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::hintsNullable() must be int, string given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => new stdClass]),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::hintsNullable() must be int, stdClass given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => []]),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::hintsNullable() must be int, array given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '1', 'bool' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $bool passed to MyPresenter::hintsNullable() must be bool, string given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $arr passed to MyPresenter::hintsNullable() must be array, string given.',
	);
});


test('', function () {
	$method = new ReflectionMethod('MyPresenter', 'hintsDefaults');

	Assert::same([0, false, '', [], []], Reflection::combineArgs($method, []));
	Assert::same([0, false, '', [], []], Reflection::combineArgs($method, ['int' => null, 'bool' => null, 'str' => null, 'arr' => null, 'iter' => null]));
	Assert::same([1, true, 'abc', [1], [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1], 'iter' => [1]]));
	Assert::same([0, false, '', [], []], Reflection::combineArgs($method, ['int' => 0, 'bool' => false, 'str' => '', 'arr' => [], 'iter' => []]));

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::hintsDefaults() must be int, string given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => new stdClass]),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::hintsDefaults() must be int, stdClass given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => []]),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::hintsDefaults() must be int, array given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '1', 'bool' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $bool passed to MyPresenter::hintsDefaults() must be bool, string given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $arr passed to MyPresenter::hintsDefaults() must be array, string given.',
	);
});


test('', function () {
	$method = new ReflectionMethod('MyPresenter', 'defaults');

	Assert::same([0, false, '', []], Reflection::combineArgs($method, []));
	Assert::same([0, false, '', []], Reflection::combineArgs($method, ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
	Assert::same([1, true, 'abc', [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
	Assert::same([0, false, '', []], Reflection::combineArgs($method, ['int' => 0, 'bool' => false, 'str' => '', 'arr' => []]));

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::defaults() must be int, string given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => new stdClass]),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::defaults() must be int, stdClass given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => []]),
		Nette\InvalidArgumentException::class,
		'Argument $int passed to MyPresenter::defaults() must be int, array given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '1', 'bool' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $bool passed to MyPresenter::defaults() must be bool, string given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $arr passed to MyPresenter::defaults() must be array, string given.',
	);
});


test('', function () {
	$method = new ReflectionMethod('MyPresenter', 'objects');

	Assert::equal([new stdClass, new stdClass, new stdClass], Reflection::combineArgs($method, ['req' => new stdClass, 'opt' => new stdClass, 'nullable' => new stdClass]));

	Assert::exception(
		fn() => Reflection::combineArgs($method, []),
		Nette\InvalidArgumentException::class,
		'Missing parameter $req required by MyPresenter::objects()',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['req' => null, 'nullable' => null, 'opt' => null]),
		Nette\InvalidArgumentException::class,
		'Missing parameter $req required by MyPresenter::objects()',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['req' => $method, 'opt' => null]),
		Nette\InvalidArgumentException::class,
		'Argument $req passed to MyPresenter::objects() must be stdClass, ReflectionMethod given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['req' => [], 'opt' => null]),
		Nette\InvalidArgumentException::class,
		'Argument $req passed to MyPresenter::objects() must be stdClass, array given.',
	);
});


test('', function () {
	$method = new ReflectionMethod('MyPresenter', 'hintsUnion');

	Assert::same([1, 'abc'], Reflection::combineArgs($method, ['intArray' => '1', 'strArray' => 'abc']));
	Assert::same([[1], [2]], Reflection::combineArgs($method, ['intArray' => [1], 'strArray' => [2]]));

	Assert::exception(
		fn() => Reflection::combineArgs($method, []),
		Nette\InvalidArgumentException::class,
		'Missing parameter $intArray required by MyPresenter::hintsUnion()',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['intArray' => '']),
		Nette\InvalidArgumentException::class,
		'Argument $intArray passed to MyPresenter::hintsUnion() must be array|int, string given.',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['intArray' => null]),
		Nette\InvalidArgumentException::class,
		'Missing parameter $intArray required by MyPresenter::hintsUnion()',
	);

	Assert::exception(
		fn() => Reflection::combineArgs($method, ['intArray' => new stdClass]),
		Nette\InvalidArgumentException::class,
		'Argument $intArray passed to MyPresenter::hintsUnion() must be array|int, stdClass given.',
	);
});
