<?php

/**
 * Test: Template filters
 */

use Nette\Bridges\ApplicationLatte\Template;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

$engine = new Latte\Engine;
$template = new Template($engine);

Assert::exception(function () use ($template) {
	@$template->neverExistingLatteDefaultFilter('abc');
}, LogicException::class, "Filter 'neverExistingLatteDefaultFilter' is not defined.");

$engine->addFilter('neverExistingLatteDefaultFilter', 'strlen');

Assert::same(3, @$template->neverExistingLatteDefaultFilter('abc'));

Assert::error(function () use ($template) {
	$template->neverExistingLatteDefaultFilter('abc');
}, E_USER_DEPRECATED, 'Invoking filters on Template object is deprecated, use getLatte()->invokeFilter().');
