<?php

/**
 * Test: Presenter::formatTemplateFiles.
 */

declare(strict_types=1);

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/one/APresenter.php';
require __DIR__ . '/one/sub/BPresenter.php';
require __DIR__ . '/two/CPresenter.php';


test('template file paths for root presenter', function () {
	$presenter = new APresenter;
	$presenter->setParent(null, 'One');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/view.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.view.latte',
	], $presenter->formatTemplateFiles());
});


test('submodule presenter template resolution', function () {
	$presenter = new BPresenter;
	$presenter->setParent(null, 'One');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/view.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.view.latte',
	], $presenter->formatTemplateFiles());
});


test('flat module structure template lookup', function () {
	$presenter = new CPresenter;
	$presenter->setParent(null, 'Two');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'two/view.latte',
	], $presenter->formatTemplateFiles());
});


test('module-prefixed presenter template paths', function () {
	$presenter = new APresenter;
	$presenter->setParent(null, 'Module:One');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/view.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.view.latte',
	], $presenter->formatTemplateFiles());
});


test('nested module template inheritance', function () {
	$presenter = new BPresenter;
	$presenter->setParent(null, 'Module:One');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/view.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.view.latte',
	], $presenter->formatTemplateFiles());
});


test('module-specific template file hierarchy', function () {
	$presenter = new CPresenter;
	$presenter->setParent(null, 'Module:Two');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'two/view.latte',
	], $presenter->formatTemplateFiles());
});
