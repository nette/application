<?php

/**
 * Test: Nette\Application\PresenterFactory.
 */

use Nette\Application\PresenterFactory;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$factory = new PresenterFactory;

test(function () use ($factory) {
	$factory->setMapping([
		'Foo2' => 'App2\*\*Presenter',
		'Foo3' => 'My\App\*Mod\*Presenter',
	]);

	Assert::same('Foo', $factory->unformatPresenterClass('FooPresenter'));
	Assert::same('Foo:Bar', $factory->unformatPresenterClass('FooModule\BarPresenter'));
	Assert::same('Foo:Bar:Baz', $factory->unformatPresenterClass('FooModule\BarModule\BazPresenter'));

	Assert::same('Foo2', $factory->unformatPresenterClass('Foo2Presenter'));
	Assert::same('Foo2:Bar', $factory->unformatPresenterClass('App2\BarPresenter'));
	Assert::same('Foo2:Bar:Baz', $factory->unformatPresenterClass('App2\Bar\BazPresenter'));

	Assert::same('Foo3:Bar', $factory->unformatPresenterClass('My\App\BarPresenter'));
	Assert::same('Foo3:Bar:Baz', $factory->unformatPresenterClass('My\App\BarMod\BazPresenter'));

	Assert::null($factory->unformatPresenterClass('Foo'));
	Assert::null($factory->unformatPresenterClass('FooMod\BarPresenter'));
});


test(function () use ($factory) {
	$factory->setMapping([
		'Foo2' => 'App2\*Presenter',
		'Foo3' => 'My\App\*Presenter',
	]);

	Assert::same('Foo2', $factory->unformatPresenterClass('Foo2Presenter'));
	Assert::same('Foo2:Bar', $factory->unformatPresenterClass('App2\BarPresenter'));
	Assert::same('Foo2:Bar:Baz', $factory->unformatPresenterClass('App2\BarModule\BazPresenter'));

	Assert::same('Foo3:Bar', $factory->unformatPresenterClass('My\App\BarPresenter'));
	Assert::same('Foo3:Bar:Baz', $factory->unformatPresenterClass('My\App\BarModule\BazPresenter'));

	Assert::null($factory->unformatPresenterClass('App2\Bar\BazPresenter'));
	Assert::null($factory->unformatPresenterClass('My\App\BarMod\BazPresenter'));
});
