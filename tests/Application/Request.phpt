<?php

/**
 * Test: Nette\Application\Request
 */

declare(strict_types=1);

use Nette\Application\Request;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('', function () {
	$request = new Request('Homepage', 'GET', ['a' => 1, 'b' => null]);

	Assert::same(1, $request->getParameter('a'));
	Assert::same(null, $request->getParameter('b'));
});


test('', function () {
	$request = new Request('Homepage', 'GET', [], ['a' => 1, 'b' => null]);

	Assert::same(['a' => 1, 'b' => null], $request->getPost());
	Assert::same(1, $request->getPost('a'));
	Assert::same(null, $request->getPost('b'));
});
