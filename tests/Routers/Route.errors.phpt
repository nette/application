<?php

/**
 * Test: Nette\Application\Routers\Route errors.
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	$route = new Route('[a');
}, Nette\InvalidArgumentException::class, "Unexpected '[' in mask '[a'.");

Assert::exception(function () {
	$route = new Route('a]');
}, Nette\InvalidArgumentException::class, "Missing '[' in mask 'a]'.");
