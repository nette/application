<?php

/**
 * Test: Nette\Application\PresenterFactory.
 */

use Nette\Application\PresenterFactory;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function () {
	$factory = new PresenterFactory;
	$factory->setMapping([
		'*' => ['App\\', 'Module\*\\', 'Presenter\*'],
	]);
	Assert::same('App\Module\Jupiter\Presenter\RedDwarf', $factory->formatPresenterClass('Jupiter:RedDwarf'));
	Assert::same('App\Module\Universe\Module\Jupiter\Presenter\RedDwarf', $factory->formatPresenterClass('Universe:Jupiter:RedDwarf'));
});

test(function () {
	$factory = new PresenterFactory;
	$factory->setMapping([
		'*' => ['App\\', '*\\', '*'],
	]);
	Assert::same('App\Universe\Jupiter\RedDwarf', $factory->formatPresenterClass('Universe:Jupiter:RedDwarf'));
});

test(function () {
	$factory = new PresenterFactory;
	$factory->setMapping([
		'*' => ['*\\', '*'],
	]);
	Assert::same('Jupiter\RedDwarf', $factory->formatPresenterClass('Jupiter:RedDwarf'));
});

test(function () {
	$factory = new PresenterFactory;
	$factory->setMapping([
		'*' => ['App\\', '*'],
	]);
	Assert::same('App\RedDwarf', $factory->formatPresenterClass('RedDwarf'));
});

test(function () {
	$factory = new PresenterFactory;
	$factory->setMapping([
		'*' => ['App\\', 'Module\*Module\\', 'Presenter\*Presenter'],
	]);
	Assert::same(
		'App\Module\JupiterModule\Presenter\RedDwarfPresenter',
		$factory->formatPresenterClass('Jupiter:RedDwarf')
	);
	Assert::same(
		'App\Module\UniverseModule\Module\JupiterModule\Presenter\RedDwarfPresenter',
		$factory->formatPresenterClass('Universe:Jupiter:RedDwarf')
	);
});


test(function () {
	$factory = new PresenterFactory;
	Assert::exception(
		function () use ($factory) {
			$factory->setMapping([
				'*' => ['Lister', 'Rimmer', 'Cat', 'Kryton'],
			]);
		},
		Nette\InvalidStateException::class,
		'Invalid length of mask array.'
	);
});
