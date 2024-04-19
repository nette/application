<?php

declare(strict_types=1);

use Nette\Application\UI\ComponentReflection;
use Nette\Application\UI\Presenter;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Presenter
{
	public function actionDefault(): void
	{

	}


	public function renderDefault(): void
	{

	}


	public function actionAltAction(): void
	{

	}


	public function renderAltRender(): void
	{

	}


	public function handleSignal(): void
	{

	}
}


$rc = new ComponentReflection(TestPresenter::class);


// getActionRenderMethod()
Assert::null($rc->getActionRenderMethod('notexists'));

Assert::equal(
	new ReflectionMethod(TestPresenter::class, 'actionDefault'),
	$rc->getActionRenderMethod('default'),
);
Assert::equal(
	new ReflectionMethod(TestPresenter::class, 'actionAltAction'),
	$rc->getActionRenderMethod('altaction'),
);

Assert::equal(
	new ReflectionMethod(TestPresenter::class, 'renderAltRender'),
	$rc->getActionRenderMethod('altrender'),
);


// getSignalMethod()
Assert::null($rc->getSignalMethod('notexists'));
Assert::equal(
	new ReflectionMethod(TestPresenter::class, 'handleSignal'),
	$rc->getSignalMethod('signal'),
);
