<?php

/**
 * Test: Nette\Application\Routers\Route errors.
 */

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	$route = new Route('[a');
}, 'Nette\InvalidArgumentException', "Unexpected '[' in mask '[a'.");

Assert::exception(function () {
	$route = new Route('a]');
}, 'Nette\InvalidArgumentException', "Missing '[' in mask 'a]'.");
