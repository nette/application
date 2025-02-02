<?php

/**
 * Test: Presenter::formatLayoutTemplateFiles.
 */

declare(strict_types=1);

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/one/APresenter.php';
require __DIR__ . '/one/sub/BPresenter.php';
require __DIR__ . '/two/CPresenter.php';


test('layout template paths for root presenter', function () {
	$presenter = new APresenter;
	$presenter->setParent(null, 'One');
	$presenter->setLayout('my');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/@my.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.@my.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/@my.latte',
	], $presenter->formatLayoutTemplateFiles());
});


test('submodule presenter layout resolution', function () {
	$presenter = new BPresenter;
	$presenter->setParent(null, 'One');
	$presenter->setLayout('my');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/@my.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.@my.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/@my.latte',
	], $presenter->formatLayoutTemplateFiles());
});


test('default layout template hierarchy', function () {
	$presenter = new CPresenter;
	$presenter->setParent(null, 'Two');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'two/@layout.latte',
		__DIR__ . '/@layout.latte',
	], $presenter->formatLayoutTemplateFiles());
});


test('multi-module layout template paths', function () {
	$presenter = new APresenter;
	$presenter->setParent(null, 'Module:SubModule:One');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/@layout.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.@layout.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/@layout.latte',
		__DIR__ . '/templates/@layout.latte',
		dirname(__DIR__) . '/templates/@layout.latte',
	], $presenter->formatLayoutTemplateFiles());
});


test('nested module template inheritance', function () {
	$presenter = new BPresenter;
	$presenter->setParent(null, 'Module:SubModule:One');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/@layout.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.@layout.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/@layout.latte',
		__DIR__ . '/templates/@layout.latte',
		dirname(__DIR__) . '/templates/@layout.latte',
	], $presenter->formatLayoutTemplateFiles());
});


test('deep module structure layout resolution', function () {
	$presenter = new CPresenter;
	$presenter->setParent(null, 'Module:SubModule:Two');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'two/@layout.latte',
		__DIR__ . '/@layout.latte',
		dirname(__DIR__) . '/@layout.latte',
		dirname(__DIR__, 2) . '/@layout.latte',
	], $presenter->formatLayoutTemplateFiles());
});


test('explicit layout file assignment', function () {
	$presenter = new BPresenter;
	$presenter->setLayout(__DIR__ . '/file.latte');

	Assert::same([
		__DIR__ . '/file.latte',
	], $presenter->formatLayoutTemplateFiles());
});
