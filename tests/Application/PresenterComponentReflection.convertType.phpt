<?php

/**
 * Test: PresenterComponentReflection::convertType()
 */

use Nette\Application\UI\PresenterComponentReflection;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


function testIt($type, $val, $res = NULL)
{
	if (func_num_args() === 3) {
		Assert::true(PresenterComponentReflection::convertType($val, $type));
		Assert::same($res, $val);
	} else {
		Assert::false(PresenterComponentReflection::convertType($val, $type));
	}
}

$obj = new stdClass;

testIt('string', NULL, NULL);
testIt('string', []);
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

testIt('int', NULL, NULL);
testIt('int', []);
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

testIt('double', NULL, NULL);
testIt('double', []);
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

testIt('bool', NULL, NULL);
testIt('bool', []);
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

testIt('array', NULL, NULL);
testIt('array', [], []);
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
