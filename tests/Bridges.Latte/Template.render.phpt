<?php

/**
 * Test: Template rendering
 */

use Nette\Bridges\ApplicationLatte\Template;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

$engine = new Latte\Engine;

// Set/get file
test(function () use ($engine) {
	$template = new Template($engine);
	$template->setFile(__DIR__ . '/templates/foo.latte');
	$output = $template->renderToString(null, ['bar' => 'world']);

	Assert::equal(__DIR__ . '/templates/foo.latte', $template->getFile());
	Assert::match('Hello world!%A%', $output);
});

// Pass file directly
test(function () use ($engine) {
	$template = new Template($engine);
	$output = $template->renderToString(__DIR__ . '/templates/foo.latte', ['bar' => 'world']);

	Assert::match('Hello world!%A%', $output);
});

// toString, (string)
test(function () use ($engine) {
	$template = new Template($engine);
	$template->setFile(__DIR__ . '/templates/foo.latte');
	$template->add('bar', 'world');

	Assert::match('Hello world!%A%', $template->__toString());
	Assert::match('Hello world!%A%', (string) $template);
});

// Exception: missing
test(function () use ($engine) {
	$template = new Template($engine);
	$template->setFile(__DIR__ . '/templates/foo.latte');

	Assert::error(function () use ($template) {
		$template->renderToString();
	}, PHP_VERSION_ID < 80000 ? E_NOTICE : E_WARNING);
});
