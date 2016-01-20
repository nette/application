<?php

/**
 * Test: PresenterComponentReflection::combineArgs()
 */

use Nette\Application\UI\PresenterComponentReflection as Reflection;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class MyPresenter
{

	public function params($int, $bool, $str, $arr)
	{
	}

	public function defaults($int = 0, $bool = FALSE, $str = '', $arr = array())
	{
	}

	public function objects(stdClass $req, stdClass $opt = NULL)
	{
	}

}


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'params');

	Assert::same(array(NULL, NULL, NULL, NULL), Reflection::combineArgs($method, array()));
	Assert::same(array(NULL, NULL, NULL, NULL), Reflection::combineArgs($method, array('int' => NULL, 'bool' => NULL, 'str' => NULL, 'arr' => NULL)));
	Assert::same(array(1, TRUE, 'abc', '1'), Reflection::combineArgs($method, array('int' => 1, 'bool' => TRUE, 'str' => 'abc', 'arr' => '1')));
	Assert::same(array(0, FALSE, '', ''), Reflection::combineArgs($method, array('int' => 0, 'bool' => FALSE, 'str' => '', 'arr' => '')));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, array('int' => array()));
	}, 'Nette\Application\BadRequestException', 'Argument $int passed to MyPresenter::params() must be scalar, array given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'defaults');

	Assert::same(array(0, FALSE, '', array()), Reflection::combineArgs($method, array()));
	Assert::same(array(0, FALSE, '', array()), Reflection::combineArgs($method, array('int' => NULL, 'bool' => NULL, 'str' => NULL, 'arr' => NULL)));
	Assert::same(array(1, TRUE, 'abc', array(1)), Reflection::combineArgs($method, array('int' => '1', 'bool' => '1', 'str' => 'abc', 'arr' => array(1))));
	Assert::same(array(0, FALSE, '', array()), Reflection::combineArgs($method, array('int' => 0, 'bool' => FALSE, 'str' => '', 'arr' => array())));

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, array('int' => ''));
	}, 'Nette\Application\BadRequestException', 'Argument $int passed to MyPresenter::defaults() must be integer, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, array('int' => '1', 'bool' => ''));
	}, 'Nette\Application\BadRequestException', 'Argument $bool passed to MyPresenter::defaults() must be boolean, string given.');

	Assert::exception(function () use ($method) {
		Reflection::combineArgs($method, array('int' => '1', 'bool' => '1', 'str' => '', 'arr' => ''));
	}, 'Nette\Application\BadRequestException', 'Argument $arr passed to MyPresenter::defaults() must be array, string given.');
});


test(function () {
	$method = new ReflectionMethod('MyPresenter', 'objects');

	Assert::same(array(NULL, NULL), Reflection::combineArgs($method, array()));
	Assert::same(array(NULL, NULL), Reflection::combineArgs($method, array('req' => NULL, 'opt' => NULL)));
	Assert::same(array($method, NULL), Reflection::combineArgs($method, array('req' => $method, 'opt' => NULL)));
	Assert::equal(array(new stdClass, new stdClass), Reflection::combineArgs($method, array('req' => new stdClass, 'opt' => new stdClass)));
});
