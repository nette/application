<?php

/**
 * Test: Nette\Application\Routers\Route & module fixity.
 */

declare(strict_types=1);

use Nette\Application\Routers\Route;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$route = new Route('auth', [
	'module' => 'Auth',
	'presenter' => 'Homepage',
	'action' => 'default',
]);

Assert::same(['presenter' => 'Auth:Homepage', 'action' => 'default'], $route->getConstantParameters());


$route = new Route('<module>', [
	'presenter' => 'Homepage',
	'action' => 'default',
]);

Assert::same(['action' => 'default'], $route->getConstantParameters());


$route = new Route('<presenter>', [
	'module' => 'Auth',
	'action' => 'default',
]);

Assert::same(['action' => 'default'], $route->getConstantParameters());
