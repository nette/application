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


	public function hints(int $int, bool $bool, string $str, array $arr)
	{
	}


	public function hintsNulls(int $int = null, bool $bool = null, string $str = null, array $arr = null)
	{
	}


	public function hintsDefaults(int $int = 0, bool $bool = false, string $str = '', array $arr = [])
	{
	}


	public function defaults($int = 0, $bool = false, $str = '', $arr = [])
	{
	}


	public function objects(stdClass $req, stdClass $opt = null)
	{
	}
}


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'params');

	Assert::same([null, null, null, null], Reflection::combineArgs($method, []));
	Assert::same([null, null, null, null], Reflection::combineArgs($method, ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
	Assert::same([1, true, 'abc', '1'], Reflection::combineArgs($method, ['int' => 1, 'bool' => true, 'str' => 'abc', 'arr' => '1']));
	Assert::same([0, false, '', ''], Reflection::combineArgs($method, ['int' => 0, 'bool' => false, 'str' => '', 'arr' => '']));
	Assert::equal([null, null, null, new stdClass], Reflection::combineArgs($method, ['arr' => new stdClass]));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => []]);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::params() must be scalar, array given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'hints');

	Assert::same([1, true, 'abc', [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
	Assert::same([0, false, '', []], Reflection::combineArgs($method, ['int' => 0, 'bool' => false, 'str' => ''])); // missing 'arr'

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, []);
	}, Nette\InvalidArgumentException::class, 'Missing parameter $int required by MyPresenter::hints()');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '']);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::hints() must be int, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => null]);
	}, Nette\InvalidArgumentException::class, 'Missing parameter $int required by MyPresenter::hints()');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => new stdClass]);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::hints() must be int, stdClass given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => []]);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::hints() must be int, array given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '']);
	}, Nette\InvalidArgumentException::class, 'Argument $bool passed to MyPresenter::hints() must be bool, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']);
	}, Nette\InvalidArgumentException::class, 'Argument $arr passed to MyPresenter::hints() must be array, string given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'hintsNulls');

	Assert::same([null, null, null, null], Reflection::combineArgs($method, []));
	Assert::same([null, null, null, null], Reflection::combineArgs($method, ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
	Assert::same([1, true, 'abc', [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
	Assert::same([0, false, '', []], Reflection::combineArgs($method, ['int' => 0, 'bool' => false, 'str' => '', 'arr' => []]));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '']);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::hintsNulls() must be int, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => new stdClass]);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::hintsNulls() must be int, stdClass given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => []]);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::hintsNulls() must be int, array given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '']);
	}, Nette\InvalidArgumentException::class, 'Argument $bool passed to MyPresenter::hintsNulls() must be bool, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']);
	}, Nette\InvalidArgumentException::class, 'Argument $arr passed to MyPresenter::hintsNulls() must be array, string given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'hintsDefaults');

	Assert::same([0, false, '', []], Reflection::combineArgs($method, []));
	Assert::same([0, false, '', []], Reflection::combineArgs($method, ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
	Assert::same([1, true, 'abc', [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
	Assert::same([0, false, '', []], Reflection::combineArgs($method, ['int' => 0, 'bool' => false, 'str' => '', 'arr' => []]));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '']);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::hintsDefaults() must be int, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => new stdClass]);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::hintsDefaults() must be int, stdClass given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => []]);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::hintsDefaults() must be int, array given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '']);
	}, Nette\InvalidArgumentException::class, 'Argument $bool passed to MyPresenter::hintsDefaults() must be bool, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']);
	}, Nette\InvalidArgumentException::class, 'Argument $arr passed to MyPresenter::hintsDefaults() must be array, string given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'defaults');

	Assert::same([0, false, '', []], Reflection::combineArgs($method, []));
	Assert::same([0, false, '', []], Reflection::combineArgs($method, ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
	Assert::same([1, true, 'abc', [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
	Assert::same([0, false, '', []], Reflection::combineArgs($method, ['int' => 0, 'bool' => false, 'str' => '', 'arr' => []]));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '']);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::defaults() must be integer, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => new stdClass]);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::defaults() must be integer, stdClass given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => []]);
	}, Nette\InvalidArgumentException::class, 'Argument $int passed to MyPresenter::defaults() must be integer, array given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '']);
	}, Nette\InvalidArgumentException::class, 'Argument $bool passed to MyPresenter::defaults() must be boolean, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']);
	}, Nette\InvalidArgumentException::class, 'Argument $arr passed to MyPresenter::defaults() must be array, string given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'objects');

	Assert::equal([new stdClass, new stdClass], Reflection::combineArgs($method, ['req' => new stdClass, 'opt' => new stdClass]));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, []);
	}, Nette\InvalidArgumentException::class, 'Missing parameter $req required by MyPresenter::objects()');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['req' => null, 'opt' => null]);
	}, Nette\InvalidArgumentException::class, 'Missing parameter $req required by MyPresenter::objects()');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['req' => $method, 'opt' => null]);
	}, Nette\InvalidArgumentException::class, 'Argument $req passed to MyPresenter::objects() must be stdClass, ReflectionMethod given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['req' => [], 'opt' => null]);
	}, Nette\InvalidArgumentException::class, 'Argument $req passed to MyPresenter::objects() must be stdClass, array given.');
});
