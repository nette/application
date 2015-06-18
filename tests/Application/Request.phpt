<?php

/**
 * Test: Nette\Application\Request
 */

use Nette\Application\Request;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function () {
	$request = new Request('Homepage', 'GET', ['a' => 1, 'b' => NULL]);

	Assert::same(1, $request->getParameter('a'));
	Assert::same(NULL, $request->getParameter('b'));
});


test(function () {
	$request = new Request('Homepage', 'GET', [], ['a' => 1, 'b' => NULL]);

	Assert::same(['a' => 1, 'b' => NULL], $request->getPost());
	Assert::same(1, $request->getPost('a'));
	Assert::same(NULL, $request->getPost('b'));
});
