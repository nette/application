<?php

/**
 * Test: Nette\Application\Routers\Route errors.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(
	fn() => new Route('[a'),
	Nette\InvalidArgumentException::class,
	"Unexpected '[' in mask '[a'.",
);

Assert::exception(
	fn() => new Route('a]'),
	Nette\InvalidArgumentException::class,
	"Missing '[' in mask 'a]'.",
);

Assert::exception(
	fn() => new Route('<presenter>/<action'),
	Nette\InvalidArgumentException::class,
	"Unexpected '/<action' in mask '<presenter>/<action'.",
);

Assert::exception(
	fn() => new Route('<presenter>/action>'),
	Nette\InvalidArgumentException::class,
	"Unexpected '/action>' in mask '<presenter>/action>'.",
);
