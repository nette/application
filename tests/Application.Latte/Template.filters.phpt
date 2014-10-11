<?php

/**
 * Test: Template filters
 */

use Nette\Bridges\ApplicationLatte\Template,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

$engine = new Latte\Engine;
$template = new Template($engine);

Assert::exception(function () use ($template) {
	@$template->length('abc');
}, 'LogicException', "Filter 'length' is not defined.");

$engine->addFilter('length', 'strlen');

Assert::same( 3, @$template->length('abc') );

Assert::error(function () use ($template) {
	$template->length('abc');
}, E_USER_DEPRECATED, 'Invoking filters on Template object is deprecated, use getLatte()->invokeFilter().');
