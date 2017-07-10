<?php

/**
 * Test: Presenter::formatLayoutTemplateFiles.
 */

use Nette\Application\UI\Presenter;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/one/Presenter1.php';
require __DIR__ . '/two/Presenter2.php';


test(function () { // with subdir templates
	$presenter = new Presenter1;
	$presenter->setParent(null, 'One');
	$presenter->setLayout('my');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/@my.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.@my.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/@my.latte',
	], $presenter->formatLayoutTemplateFiles());
});


test(function () { // without subdir templates
	$presenter = new Presenter2;
	$presenter->setParent(null, 'Two');

	Assert::same([
		__DIR__ . '/templates/Two/@layout.latte',
		__DIR__ . '/templates/Two.@layout.latte',
		__DIR__ . '/templates/@layout.latte',
	], $presenter->formatLayoutTemplateFiles());
});


test(function () { // with module & subdir templates
	$presenter = new Presenter1;
	$presenter->setParent(null, 'Module:SubModule:One');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/@layout.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.@layout.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/@layout.latte',
		__DIR__ . '/templates/@layout.latte',
		dirname(__DIR__) . '/templates/@layout.latte',
	], $presenter->formatLayoutTemplateFiles());
});


test(function () { // with module & without subdir templates
	$presenter = new Presenter2;
	$presenter->setParent(null, 'Module:SubModule:Two');

	Assert::same([
		__DIR__ . '/templates/Two/@layout.latte',
		__DIR__ . '/templates/Two.@layout.latte',
		__DIR__ . '/templates/@layout.latte',
		dirname(__DIR__) . '/templates/@layout.latte',
		dirname(dirname(__DIR__)) . '/templates/@layout.latte',
	], $presenter->formatLayoutTemplateFiles());
});


test(function () { // direct file
	$presenter = new Presenter2;
	$presenter->setLayout(__DIR__ . '/file.latte');

	Assert::same([
		__DIR__ . '/file.latte'
	], $presenter->formatLayoutTemplateFiles());
});
