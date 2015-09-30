<?php

/**
 * Test: PresenterComponentReflection::convertType()
 */

use Nette\Application\UI\PresenterComponentReflection;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


//               [$type]  null   scalar     array  object     callable
//   [$val] ----------------------------------------------------------
//   null                 pass   cast       cast   deny       deny
//   scalar               pass   cast/deny  deny   deny       deny
//   array                deny   deny       pass   deny       deny
//   object               pass   deny       deny   pass/deny  deny


function testIt($type, $val, $res = NULL)
{
	if (func_num_args() === 3) {
		Assert::true(PresenterComponentReflection::convertType($val, $type));
	} else {
		$res = $val;
		Assert::false(PresenterComponentReflection::convertType($val, $type));
	}
	Assert::same($res, $val);
}

$obj = new stdClass;

testIt('string', NULL, '');
testIt('string', []);
testIt('string', $obj);
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

testIt('int', NULL, 0);
testIt('int', []);
testIt('int', $obj);
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

testIt('double', NULL, 0.0);
testIt('double', []);
testIt('double', $obj);
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

testIt('bool', NULL, FALSE);
testIt('bool', []);
testIt('bool', $obj);
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

testIt('array', NULL, []);
testIt('array', [], []);
testIt('array', $obj);
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
testIt('callable', []);
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

$var = NULL;
Assert::false(PresenterComponentReflection::convertType($var, 'stdClass', TRUE));
$var = [];
Assert::false(PresenterComponentReflection::convertType($var, 'stdClass', TRUE));
Assert::true(PresenterComponentReflection::convertType($obj, 'stdClass', TRUE));
$var = function () {};
Assert::false(PresenterComponentReflection::convertType($var, 'stdClass', TRUE));
Assert::true(PresenterComponentReflection::convertType($var, 'Closure', TRUE));
$var = '';
Assert::false(PresenterComponentReflection::convertType($var, 'stdClass', TRUE));
$var = '1a';
Assert::false(PresenterComponentReflection::convertType($var, 'stdClass', TRUE));
$var = TRUE;
Assert::false(PresenterComponentReflection::convertType($var, 'stdClass', TRUE));
$var = 0;
Assert::false(PresenterComponentReflection::convertType($var, 'stdClass', TRUE));
