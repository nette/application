<?php

/**
 * Test: PresenterComponentReflection::convertType()
 */

use Nette\Application\UI\PresenterComponentReflection;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


//               [$type]  null   scalar     array  object     callable
//   [$val] ----------------------------------------------------------
//   null                 pass   deny       deny   error      deny
//   scalar               pass   cast/deny  deny   error      deny
//   array                deny   deny       pass   deny       deny
//   object               pass   pass/error deny   pass/error deny
//
//   error = E_RECOVERABLE_ERROR


function testIt($type, $val, $res = NULL)
{
	$isClass = class_exists($type);
	if (func_num_args() === 3) {
		Assert::true(PresenterComponentReflection::convertType($val, $type, $isClass));
	} else {
		$res = $val;
		Assert::false(PresenterComponentReflection::convertType($val, $type, $isClass));
	}
	Assert::same($res, $val);
}

$obj = new stdClass;

testIt('NULL', NULL, NULL);
testIt('NULL', array());
testIt('NULL', $obj, $obj);
testIt('NULL', '', '');
testIt('NULL', 'a', 'a');
testIt('NULL', '0', '0');
testIt('NULL', '1', '1');
testIt('NULL', '1.0', '1.0');
testIt('NULL', '1.1', '1.1');
testIt('NULL', '1a', '1a');
testIt('NULL', TRUE, TRUE);
testIt('NULL', FALSE, FALSE);
testIt('NULL', 0, 0);
testIt('NULL', 1, 1);
testIt('NULL', 1.0, 1.0);
testIt('NULL', 1.2, 1.2);

testIt('string', NULL);
testIt('string', array());
testIt('string', $obj, $obj);
testIt('string', '', '');
testIt('string', 'a', 'a');
testIt('string', '0', '0');
testIt('string', '1', '1');
testIt('string', '1.0', '1.0');
testIt('string', '1.1', '1.1');
testIt('string', '1a', '1a');
testIt('string', TRUE, '1');
testIt('string', FALSE, '0');
testIt('string', 0, '0');
testIt('string', 1, '1');
testIt('string', 1.0, '1');
testIt('string', 1.2, '1.2');

testIt('int', NULL);
testIt('int', array());
testIt('int', $obj, $obj);
testIt('int', '');
testIt('int', 'a');
testIt('int', '0', 0);
testIt('int', '1', 1);
testIt('int', '1.0');
testIt('int', '1.1');
testIt('int', '1a');
testIt('int', TRUE, 1);
testIt('int', FALSE, 0);
testIt('int', 0, 0);
testIt('int', 1, 1);
testIt('int', 1.0, 1);
testIt('int', 1.2);

testIt('double', NULL);
testIt('double', array());
testIt('double', $obj, $obj);
testIt('double', '');
testIt('double', 'a');
testIt('double', '0', 0.0);
testIt('double', '1', 1.0);
testIt('double', '1.0');
testIt('double', '1.1', 1.1);
testIt('double', '1a');
testIt('double', TRUE, 1.0);
testIt('double', FALSE, 0.0);
testIt('double', 0, 0.0);
testIt('double', 1, 1.0);
testIt('double', 1.0, 1.0);
testIt('double', 1.2, 1.2);

testIt('bool', NULL);
testIt('bool', array());
testIt('bool', $obj, $obj);
testIt('bool', '');
testIt('bool', 'a');
testIt('bool', '1', TRUE);
testIt('bool', '1.0');
testIt('bool', '1.1');
testIt('bool', '1a');
testIt('bool', TRUE, TRUE);
testIt('bool', FALSE, FALSE);
testIt('bool', 0, FALSE);
testIt('bool', 1, TRUE);
testIt('bool', 1.0, TRUE);
testIt('bool', 1.2);

testIt('array', NULL);
testIt('array', array(), array());
testIt('array', $obj, $obj);
testIt('array', '');
testIt('array', 'a');
testIt('array', '1');
testIt('array', '1.0');
testIt('array', '1.1');
testIt('array', '1a');
testIt('array', TRUE);
testIt('array', FALSE);
testIt('array', 0);
testIt('array', 1);
testIt('array', 1.0);
testIt('array', 1.2);

testIt('callable', NULL);
testIt('callable', array());
testIt('callable', $obj);
testIt('callable', function () {});
testIt('callable', '');
testIt('callable', 'trim');
testIt('callable', '1');
testIt('callable', '1.0');
testIt('callable', '1.1');
testIt('callable', '1a');
testIt('callable', TRUE);
testIt('callable', FALSE);
testIt('callable', 0);
testIt('callable', 1);
testIt('callable', 1.0);
testIt('callable', 1.2);

testIt('stdClass', NULL, NULL);
testIt('stdClass', array());
testIt('stdClass', $obj, $obj);
testIt('stdClass', $var = function () {}, $var);
testIt('stdClass', '', '');
testIt('stdClass', 'a', 'a');
testIt('stdClass', '1', '1');
testIt('stdClass', '1.0', '1.0');
testIt('stdClass', '1.1', '1.1');
testIt('stdClass', '1a', '1a');
testIt('stdClass', TRUE, TRUE);
testIt('stdClass', FALSE, FALSE);
testIt('stdClass', 0, 0);
testIt('stdClass', 1, 1);
testIt('stdClass', 1.0, 1.0);
testIt('stdClass', 1.2, 1.2);

testIt('Closure', $var = function () {}, $var);
