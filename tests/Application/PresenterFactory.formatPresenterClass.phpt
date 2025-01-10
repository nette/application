<?php

/**
 * Test: Nette\Application\PresenterFactory.
 */

declare(strict_types=1);

use Nette\Application\PresenterFactory;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('defined module', function () {
	$factory = new PresenterFactory;

	$factory->setMapping([
		'Foo2' => 'App2\*\*Presenter',
		'Foo3' => 'My\App\*Mod\*Presenter',
		'Foo4' => 'My\App\*Mod\*Presenter',
		'Foo4:Bar' => 'Other\App\*Mod\*Presenter',
	]);

	Assert::same('App\UI\Foo\FooPresenter', $factory->formatPresenterClass('Foo'));
	Assert::same('App\UI\Foo\Bar\BarPresenter', $factory->formatPresenterClass('Foo:Bar'));
	Assert::same('App\UI\Foo\Bar\Baz\BazPresenter', $factory->formatPresenterClass('Foo:Bar:Baz'));

	Assert::same('App\UI\Foo2\Foo2Presenter', $factory->formatPresenterClass('Foo2'));
	Assert::same('App2\BarPresenter', $factory->formatPresenterClass('Foo2:Bar'));
	Assert::same('App2\Bar\BazPresenter', $factory->formatPresenterClass('Foo2:Bar:Baz'));

	Assert::same('My\App\BarPresenter', $factory->formatPresenterClass('Foo3:Bar'));
	Assert::same('My\App\BarMod\BazPresenter', $factory->formatPresenterClass('Foo3:Bar:Baz'));

    Assert::same('My\App\FooPresenter', $factory->formatPresenterClass('Foo4:Foo'));
    Assert::same('My\App\FooMod\BarPresenter', $factory->formatPresenterClass('Foo4:Foo:Bar'));

	Assert::same('Other\App\BazPresenter', $factory->formatPresenterClass('Foo4:Bar:Baz'));
    Assert::same('Other\App\BazMod\FooPresenter', $factory->formatPresenterClass('Foo4:Bar:Baz:Foo'));

	Assert::same('NetteModule\FooPresenter', $factory->formatPresenterClass('Nette:Foo'));
});


test('auto module', function () {
	$factory = new PresenterFactory;

	$factory->setMapping([
		'Foo2' => 'App2\*Presenter',
		'Foo3' => 'My\App\*Presenter',
		'Foo4' => 'My\App\*Presenter',
		'Foo4:Bar' => 'Other\App\*Presenter',
	]);

	Assert::same('App\UI\Foo2\Foo2Presenter', $factory->formatPresenterClass('Foo2'));
	Assert::same('App2\BarPresenter', $factory->formatPresenterClass('Foo2:Bar'));
	Assert::same('App2\BarModule\BazPresenter', $factory->formatPresenterClass('Foo2:Bar:Baz'));

	Assert::same('My\App\BarPresenter', $factory->formatPresenterClass('Foo3:Bar'));
	Assert::same('My\App\BarModule\BazPresenter', $factory->formatPresenterClass('Foo3:Bar:Baz'));

    Assert::same('My\App\FooPresenter', $factory->formatPresenterClass('Foo4:Foo'));
    Assert::same('My\App\FooModule\BarPresenter', $factory->formatPresenterClass('Foo4:Foo:Bar'));

	Assert::same('Other\App\BazPresenter', $factory->formatPresenterClass('Foo4:Bar:Baz'));
	Assert::same('Other\App\BazModule\FooPresenter', $factory->formatPresenterClass('Foo4:Bar:Baz:Foo'));
});


test('location ** & defined module', function () {
	$factory = new PresenterFactory;

	$factory->setMapping([
		'Foo2' => 'App2\*\**Presenter',
		'Foo3' => 'My\App\*Mod\**Presenter',
		'Foo4' => 'My\App\*Mod\**Presenter',
		'Foo4:Bar' => 'Other\App\*Mod\**Presenter',
	]);

	Assert::same('App\UI\Foo\FooPresenter', $factory->formatPresenterClass('Foo'));
	Assert::same('App\UI\Foo\Bar\BarPresenter', $factory->formatPresenterClass('Foo:Bar'));
	Assert::same('App\UI\Foo\Bar\Baz\BazPresenter', $factory->formatPresenterClass('Foo:Bar:Baz'));

	Assert::same('App\UI\Foo2\Foo2Presenter', $factory->formatPresenterClass('Foo2'));
	Assert::same('App2\Bar\BarPresenter', $factory->formatPresenterClass('Foo2:Bar'));
	Assert::same('App2\Bar\Baz\BazPresenter', $factory->formatPresenterClass('Foo2:Bar:Baz'));

	Assert::same('My\App\Bar\BarPresenter', $factory->formatPresenterClass('Foo3:Bar'));
	Assert::same('My\App\BarMod\Baz\BazPresenter', $factory->formatPresenterClass('Foo3:Bar:Baz'));

    Assert::same('My\App\Foo\FooPresenter', $factory->formatPresenterClass('Foo4:Foo'));
    Assert::same('My\App\FooMod\Bar\BarPresenter', $factory->formatPresenterClass('Foo4:Foo:Bar'));

	Assert::same('Other\App\Baz\BazPresenter', $factory->formatPresenterClass('Foo4:Bar:Baz'));
    Assert::same('Other\App\BazMod\Foo\FooPresenter', $factory->formatPresenterClass('Foo4:Bar:Baz:Foo'));

	Assert::same('NetteModule\FooPresenter', $factory->formatPresenterClass('Nette:Foo'));
});


test('location ** & auto module', function () {
	$factory = new PresenterFactory;

	$factory->setMapping([
		'*' => '**Presenter',
		'Foo2' => 'App2\**Presenter',
		'Foo3' => 'My\App\**Presenter',
		'Foo4' => 'My\App\**Presenter',
		'Foo4:Bar' => 'Other\App\**Presenter',
	]);

	Assert::same('Foo2\Foo2Presenter', $factory->formatPresenterClass('Foo2'));
	Assert::same('App2\Bar\BarPresenter', $factory->formatPresenterClass('Foo2:Bar'));
	Assert::same('App2\BarModule\Baz\BazPresenter', $factory->formatPresenterClass('Foo2:Bar:Baz'));

	Assert::same('My\App\Bar\BarPresenter', $factory->formatPresenterClass('Foo3:Bar'));
	Assert::same('My\App\BarModule\Baz\BazPresenter', $factory->formatPresenterClass('Foo3:Bar:Baz'));

    Assert::same('My\App\Foo\FooPresenter', $factory->formatPresenterClass('Foo4:Foo'));
    Assert::same('My\App\FooModule\Bar\BarPresenter', $factory->formatPresenterClass('Foo4:Foo:Bar'));

	Assert::same('Other\App\Baz\BazPresenter', $factory->formatPresenterClass('Foo4:Bar:Baz'));
	Assert::same('Other\App\BazModule\Foo\FooPresenter', $factory->formatPresenterClass('Foo4:Bar:Baz:Foo'));
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
