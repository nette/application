<?php

/**
 * Test: Presenter::formatTemplateFiles.
 */

declare(strict_types=1);

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/one/APresenter.php';
require __DIR__ . '/two/BPresenter.php';


test('with subdir templates', function () {
	$presenter = new APresenter;
	$presenter->setParent(null, 'One');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/view.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.view.latte',
	], $presenter->formatTemplateFiles());
});


test('without subdir templates', function () {
	$presenter = new BPresenter;
	$presenter->setParent(null, 'Two');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . '/templates/Two/view.latte',
		__DIR__ . '/templates/Two.view.latte',
	], $presenter->formatTemplateFiles());
});


test('with module & subdir templates', function () {
	$presenter = new APresenter;
	$presenter->setParent(null, 'Module:One');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/view.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.view.latte',
	], $presenter->formatTemplateFiles());
});


test('with module & without subdir templates', function () {
	$presenter = new BPresenter;
	$presenter->setParent(null, 'Module:Two');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . '/templates/Two/view.latte',
		__DIR__ . '/templates/Two.view.latte',
	], $presenter->formatTemplateFiles());
});
