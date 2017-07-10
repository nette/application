<?php

/**
 * Test: ComponentReflection::combineArgs()
 */

use Nette\Application\BadRequestException;
use Nette\Application\UI\ComponentReflection as Reflection;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class MyPresenter
{
	public function params($int, $bool, $str, $arr)
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

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => []]);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::params() must be scalar, array given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'defaults');

	Assert::same([0, false, '', []], Reflection::combineArgs($method, []));
	Assert::same([0, false, '', []], Reflection::combineArgs($method, ['int' => null, 'bool' => null, 'str' => null, 'arr' => null]));
	Assert::same([1, true, 'abc', [1]], Reflection::combineArgs($method, ['int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => [1]]));
	Assert::same([0, false, '', []], Reflection::combineArgs($method, ['int' => 0, 'bool' => false, 'str' => '', 'arr' => []]));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['int' => '']);
	}, BadRequestException::class, 'Argument $int passed to MyPresenter::defaults() must be integer, string given.');

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
		Reflection::combineArgs($method, ['req' => null, 'opt' => null]);
	}, BadRequestException::class, 'Missing parameter $req required by MyPresenter::objects()');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, ['req' => $method, 'opt' => null]);
	}, BadRequestException::class, 'Argument $req passed to MyPresenter::objects() must be stdClass, ReflectionMethod given.');
});
