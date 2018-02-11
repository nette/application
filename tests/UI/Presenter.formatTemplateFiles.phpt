<?php

/**
 * Test: Presenter::formatTemplateFiles.
 */

declare(strict_types=1);

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/one/Presenter1.php';
require __DIR__ . '/two/Presenter2.php';


test(function () { // with subdir templates
	$presenter = new Presenter1;
	$presenter->setParent(null, 'One');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/view.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.view.latte',
	], $presenter->formatTemplateFiles());
});


test(function () { // without subdir templates
	$presenter = new Presenter2;
	$presenter->setParent(null, 'Two');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . '/templates/Two/view.latte',
		__DIR__ . '/templates/Two.view.latte',
	], $presenter->formatTemplateFiles());
});


test(function () { // with module & subdir templates
	$presenter = new Presenter1;
	$presenter->setParent(null, 'Module:One');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/view.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.view.latte',
	], $presenter->formatTemplateFiles());
});


test(function () { // with module & without subdir templates
	$presenter = new Presenter2;
	$presenter->setParent(null, 'Module:Two');
	$presenter->setView('view');

	Assert::same([
		__DIR__ . '/templates/Two/view.latte',
		__DIR__ . '/templates/Two.view.latte',
	], $presenter->formatTemplateFiles());
});
