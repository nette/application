<?php

/**
 * Test: PresenterComponentReflection::combineArgs()
 * @phpVersion 7
 */

use Nette\Application\UI\PresenterComponentReflection as Reflection;
use Nette\Application\BadRequestException;
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

	public function hintsNulls(int $int = NULL, bool $bool = NULL, string $str = NULL, array $arr = NULL)
	{
	}

	public function hintsDefaults(int $int = 0, bool $bool = FALSE, string $str = '', array $arr = [])
	{
	}

	public function defaults($int = 0, $bool = FALSE, $str = '', $arr = [])
	{
	}

	public function objects(stdClass $req, stdClass $opt = NULL)
	{
	}

}


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'params');

	Assert::same([NULL, NULL, NULL, NULL], Reflection::combineArgs($method, []));
	Assert::same([NULL, NULL, NULL, NULL], Reflection::combineArgs($method, ['int' => NULL, 'bool' => NULL, 'str' => NULL, 'arr' => NULL]));
	Assert::same([1, TRUE, 'abc', '1'], Reflection::combineArgs($method, ['int' => 1, 'bool' => TRUE, 'str' => 'abc', 'arr' => '1']));
	Assert::same([0, FALSE, '', ''], Reflection::combineArgs($method, ['int' => 0, 'bool' => FALSE, 'str' => '', 'arr' => '']));
	Assert::equal([NULL, NULL, NULL, new stdClass], Reflection::combineArgs($method, ['arr' => new stdClass]));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => []]);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::params() must be scalar, array given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'hints');

	Assert::same([1, TRUE, 'abc', [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
	Assert::same([0, FALSE, '', []], Reflection::combineArgs($method, ['int' => 0, 'bool' => FALSE, 'str' => ''])); // missing 'arr'

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, []);
	}, BadRequestException::class, 'Missing parameter $int required by MyPresenter::hints()');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '']);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::hints() must be int, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => NULL]);
	}, BadRequestException::class, 'Missing parameter $int required by MyPresenter::hints()');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => new stdClass]);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::hints() must be int, stdClass given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => []]);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::hints() must be int, array given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '']);
	}, BadRequestException::class, 'Argument $bool passed to MyPresenter::hints() must be bool, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']);
	}, BadRequestException::class, 'Argument $arr passed to MyPresenter::hints() must be array, string given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'hintsNulls');

	Assert::same([NULL, NULL, NULL, NULL], Reflection::combineArgs($method, []));
	Assert::same([NULL, NULL, NULL, NULL], Reflection::combineArgs($method, ['int' => NULL, 'bool' => NULL, 'str' => NULL, 'arr' => NULL]));
	Assert::same([1, TRUE, 'abc', [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
	Assert::same([0, FALSE, '', []], Reflection::combineArgs($method, ['int' => 0, 'bool' => FALSE, 'str' => '', 'arr' => []]));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '']);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::hintsNulls() must be int, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => new stdClass]);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::hintsNulls() must be int, stdClass given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => []]);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::hintsNulls() must be int, array given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '']);
	}, BadRequestException::class, 'Argument $bool passed to MyPresenter::hintsNulls() must be bool, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']);
	}, BadRequestException::class, 'Argument $arr passed to MyPresenter::hintsNulls() must be array, string given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'hintsDefaults');

	Assert::same([0, FALSE, '', []], Reflection::combineArgs($method, []));
	Assert::same([0, FALSE, '', []], Reflection::combineArgs($method, ['int' => NULL, 'bool' => NULL, 'str' => NULL, 'arr' => NULL]));
	Assert::same([1, TRUE, 'abc', [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
	Assert::same([0, FALSE, '', []], Reflection::combineArgs($method, ['int' => 0, 'bool' => FALSE, 'str' => '', 'arr' => []]));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '']);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::hintsDefaults() must be int, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => new stdClass]);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::hintsDefaults() must be int, stdClass given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => []]);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::hintsDefaults() must be int, array given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '']);
	}, BadRequestException::class, 'Argument $bool passed to MyPresenter::hintsDefaults() must be bool, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']);
	}, BadRequestException::class, 'Argument $arr passed to MyPresenter::hintsDefaults() must be array, string given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'defaults');

	Assert::same([0, FALSE, '', []], Reflection::combineArgs($method, []));
	Assert::same([0, FALSE, '', []], Reflection::combineArgs($method, ['int' => NULL, 'bool' => NULL, 'str' => NULL, 'arr' => NULL]));
	Assert::same([1, TRUE, 'abc', [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
	Assert::same([0, FALSE, '', []], Reflection::combineArgs($method, ['int' => 0, 'bool' => FALSE, 'str' => '', 'arr' => []]));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '']);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::defaults() must be integer, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => new stdClass]);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::defaults() must be integer, stdClass given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => []]);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::defaults() must be integer, array given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '']);
	}, BadRequestException::class, 'Argument $bool passed to MyPresenter::defaults() must be boolean, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => '', 'arr' => '']);
	}, BadRequestException::class, 'Argument $arr passed to MyPresenter::defaults() must be array, string given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'objects');

	Assert::equal([new stdClass, new stdClass], Reflection::combineArgs($method, ['req' => new stdClass, 'opt' => new stdClass]));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, []);
	}, BadRequestException::class, 'Missing parameter $req required by MyPresenter::objects()');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['req' => NULL, 'opt' => NULL]);
	}, BadRequestException::class, 'Missing parameter $req required by MyPresenter::objects()');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['req' => $method, 'opt' => NULL]);
	}, BadRequestException::class, 'Argument $req passed to MyPresenter::objects() must be stdClass, ReflectionMethod given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['req' => [], 'opt' => NULL]);
	}, BadRequestException::class, 'Argument $req passed to MyPresenter::objects() must be stdClass, array given.');
});
