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
	@$template->undefinedFilter('abc');
}, LogicException::class, "Filter 'undefinedFilter' is not defined.");

$engine->addFilter('undefinedFilter', 'strlen');

Assert::same(3, @$template->undefinedFilter('abc'));

Assert::error(function () use ($template) {
	$template->undefinedFilter('abc');
}, E_USER_DEPRECATED, 'Invoking filters on Template object is deprecated, use getLatte()->invokeFilter().');
