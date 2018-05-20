<?php

/**
 * Test: Template parameters
 */

use Nette\Bridges\ApplicationLatte\Template;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

$engine = new Latte\Engine;

$template = new Template($engine);

// Empty parameters at new instance
Assert::equal([], $template->getParameters());

// Set first parameters
$template->setParameters(['foo' => 'bar']);
Assert::equal(['foo' => 'bar'], $template->getParameters());

// Append some parameter
$template->add('baz', 'baz');
Assert::equal(['foo' => 'bar', 'baz' => 'baz'], $template->getParameters());

// Duplicate parameter
Assert::throws(function () use ($template) {
	$template->add('baz', 'baz');
}, Nette\InvalidStateException::class, "The variable 'baz' already exists.");


// Magic methods
Assert::true(isset($template->foo), 'There should be foo variable');
Assert::false(isset($template->foofoo), 'There should not be foofoo variable');
Assert::equal('bar', $template->foo);

unset($template->foo);
Assert::false(isset($template->foo), 'There should not be foo variable');

Assert::error(function () use ($template) {
	echo $template->foo;
}, E_USER_NOTICE, "The variable 'foo' does not exist in template.");
