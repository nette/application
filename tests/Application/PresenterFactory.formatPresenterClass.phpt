<?php

/**
 * Test: Nette\Application\PresenterFactory.
 */

declare(strict_types=1);

use Nette\Application\PresenterFactory;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('', function () {
	$factory = new PresenterFactory;

	$factory->setMapping([
		'Foo2' => 'App2\*\*Presenter',
		'Foo3' => 'My\App\*Mod\*Presenter',
	]);

	Assert::same('FooPresenter', $factory->formatPresenterClass('Foo'));
	Assert::same('FooModule\BarPresenter', $factory->formatPresenterClass('Foo:Bar'));
	Assert::same('FooModule\BarModule\BazPresenter', $factory->formatPresenterClass('Foo:Bar:Baz'));

	Assert::same('Foo2Presenter', $factory->formatPresenterClass('Foo2'));
	Assert::same('App2\BarPresenter', $factory->formatPresenterClass('Foo2:Bar'));
	Assert::same('App2\Bar\BazPresenter', $factory->formatPresenterClass('Foo2:Bar:Baz'));

	Assert::same('My\App\BarPresenter', $factory->formatPresenterClass('Foo3:Bar'));
	Assert::same('My\App\BarMod\BazPresenter', $factory->formatPresenterClass('Foo3:Bar:Baz'));

	Assert::same('NetteModule\FooPresenter', $factory->formatPresenterClass('Nette:Foo'));
});


test('', function () {
	$factory = new PresenterFactory;

	$factory->setMapping([
		'Foo2' => 'App2\*Presenter',
		'Foo3' => 'My\App\*Presenter',
	]);

	Assert::same('Foo2Presenter', $factory->formatPresenterClass('Foo2'));
	Assert::same('App2\BarPresenter', $factory->formatPresenterClass('Foo2:Bar'));
	Assert::same('App2\BarModule\BazPresenter', $factory->formatPresenterClass('Foo2:Bar:Baz'));

	Assert::same('My\App\BarPresenter', $factory->formatPresenterClass('Foo3:Bar'));
	Assert::same('My\App\BarModule\BazPresenter', $factory->formatPresenterClass('Foo3:Bar:Baz'));
});


test('', function () {
	$factory = new PresenterFactory;
	$factory->setMapping([
		'*' => ['App', 'Module\*', 'Presenter\*'],
	]);
	Assert::same('App\Module\Foo\Presenter\Bar', $factory->formatPresenterClass('Foo:Bar'));
	Assert::same('App\Module\Universe\Module\Foo\Presenter\Bar', $factory->formatPresenterClass('Universe:Foo:Bar'));
});


test('', function () {
	$factory = new PresenterFactory;
	$factory->setMapping([
		'*' => ['', '*', '*'],
	]);
	Assert::same('Module\Foo\Bar', $factory->formatPresenterClass('Module:Foo:Bar'));
});


Assert::exception(
	function () {
		$factory = new PresenterFactory;
		$factory->setMapping([
			'*' => ['*', '*'],
		]);
	},
	Nette\InvalidStateException::class,
	'Invalid mapping mask for module *.',
);
