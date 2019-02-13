<?php

/**
 * Test: ComponentReflection::convertType()
 */

declare(strict_types=1);

use Nette\Application\UI\ComponentReflection;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


//               [$type]  null   scalar     array  object     callable
//   [$val] ----------------------------------------------------------
//   null                 pass   deny       deny   deny       deny
//   scalar               pass   cast/deny  deny   deny       deny
//   array                deny   deny       pass   deny       deny
//   object               pass   deny       deny   pass/deny  deny


function testIt(string $type, $val, $res = null)
{
	$isClass = class_exists($type);
	if (func_num_args() === 3) {
		Assert::true(ComponentReflection::convertType($val, $type, $isClass));
	} else {
		$res = $val;
		Assert::false(ComponentReflection::convertType($val, $type, $isClass));
	}
	Assert::same($res, $val);
}


$obj = new stdClass;

testIt('NULL', null, null);
testIt('NULL', []);
testIt('NULL', $obj, $obj);
testIt('NULL', '', '');
testIt('NULL', 'a', 'a');
testIt('NULL', '0', '0');
testIt('NULL', '1', '1');
testIt('NULL', '1.0', '1.0');
testIt('NULL', '1.1', '1.1');
testIt('NULL', '1a', '1a');
testIt('NULL', true, true);
testIt('NULL', false, false);
testIt('NULL', 0, 0);
testIt('NULL', 1, 1);
testIt('NULL', 1.0, 1.0);
testIt('NULL', 1.2, 1.2);

testIt('string', null);
testIt('string', []);
testIt('string', $obj);
testIt('string', '', '');
testIt('string', 'a', 'a');
testIt('string', '0', '0');
testIt('string', '1', '1');
testIt('string', '1.0', '1.0');
testIt('string', '1.1', '1.1');
testIt('string', '1a', '1a');
testIt('string', true, '1');
testIt('string', false, '0');
testIt('string', 0, '0');
testIt('string', 1, '1');
testIt('string', 1.0, '1');
testIt('string', 1.2, '1.2');

testIt('int', null);
testIt('int', []);
testIt('int', $obj);
testIt('int', '');
testIt('int', 'a');
testIt('int', '0', 0);
testIt('int', '1', 1);
testIt('int', '1.0');
testIt('int', '1.1');
testIt('int', '1a');
testIt('int', true, 1);
testIt('int', false, 0);
testIt('int', 0, 0);
testIt('int', 1, 1);
testIt('int', 1.0, 1);
testIt('int', 1.2);

testIt('double', null);
testIt('double', []);
testIt('double', $obj);
testIt('double', '');
testIt('double', 'a');
testIt('double', '0', 0.0);
testIt('double', '1', 1.0);
testIt('double', '1.', 1.0);
testIt('double', '1.0', 1.0);
testIt('double', '1.00', 1.0);
testIt('double', '1..0');
testIt('double', '1.1', 1.1);
testIt('double', '1a');
testIt('double', true, 1.0);
testIt('double', false, 0.0);
testIt('double', 0, 0.0);
testIt('double', 1, 1.0);
testIt('double', 1.0, 1.0);
testIt('double', 1.2, 1.2);

testIt('float', null);
testIt('float', []);
testIt('float', $obj);
testIt('float', '');
testIt('float', 'a');
testIt('float', '0', 0.0);
testIt('float', '1', 1.0);
testIt('float', '1.', 1.0);
testIt('float', '1.0', 1.0);
testIt('float', '1.00', 1.0);
testIt('float', '1..0');
testIt('float', '1.1', 1.1);
testIt('float', '1a');
testIt('float', true, 1.0);
testIt('float', false, 0.0);
testIt('float', 0, 0.0);
testIt('float', 1, 1.0);
testIt('float', 1.0, 1.0);
testIt('float', 1.2, 1.2);

testIt('bool', null);
testIt('bool', []);
testIt('bool', $obj);
testIt('bool', '');
testIt('bool', 'a');
testIt('bool', '1', true);
testIt('bool', '1.0');
testIt('bool', '1.1');
testIt('bool', '1a');
testIt('bool', true, true);
testIt('bool', false, false);
testIt('bool', 0, false);
testIt('bool', 1, true);
testIt('bool', 1.0, true);
testIt('bool', 1.2);

testIt('array', null);
testIt('array', [], []);
testIt('array', $obj);
testIt('array', '');
testIt('array', 'a');
testIt('array', '1');
testIt('array', '1.0');
testIt('array', '1.1');
testIt('array', '1a');
testIt('array', true);
testIt('array', false);
testIt('array', 0);
testIt('array', 1);
testIt('array', 1.0);
testIt('array', 1.2);

testIt('iterable', null);
testIt('iterable', [], []);
testIt('iterable', $obj);
testIt('iterable', '');
testIt('iterable', 'a');
testIt('iterable', '1');
testIt('iterable', '1.0');
testIt('iterable', '1.1');
testIt('iterable', '1a');
testIt('iterable', true);
testIt('iterable', false);
testIt('iterable', 0);
testIt('iterable', 1);
testIt('iterable', 1.0);
testIt('iterable', 1.2);

testIt('callable', null);
testIt('callable', []);
testIt('callable', $obj);
testIt('callable', function () {});
testIt('callable', '');
testIt('callable', 'trim');
testIt('callable', '1');
testIt('callable', '1.0');
testIt('callable', '1.1');
testIt('callable', '1a');
testIt('callable', true);
testIt('callable', false);
testIt('callable', 0);
testIt('callable', 1);
testIt('callable', 1.0);
testIt('callable', 1.2);

testIt('stdClass', null);
testIt('stdClass', []);
testIt('stdClass', $obj, $obj);
testIt('stdClass', function () {});
testIt('stdClass', '');
testIt('stdClass', 'a');
testIt('stdClass', '1');
testIt('stdClass', '1.0');
testIt('stdClass', '1.1');
testIt('stdClass', '1a');
testIt('stdClass', true);
testIt('stdClass', false);
testIt('stdClass', 0);
testIt('stdClass', 1);
testIt('stdClass', 1.0);
testIt('stdClass', 1.2);

testIt('Closure', $var = function () {}, $var);
