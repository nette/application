<?php

/**
 * Test: Nette\Application\UI\Component::isLinkCurrent()
 * @phpVersion 7
 */

use Nette\Application;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/MockPresenterFactory.php';

class TestPresenter extends Application\UI\Presenter
{

	public function actionDefault(int $int, bool $bool)
	{
	}

	public function handleSignal()
	{
	}

	public function handleOtherSignal()
	{
	}

}

class TestControl extends Application\UI\Control
{

	public function handleClick(int $x)
	{
	}

	public function handleOtherSignal()
	{
	}

}

require __DIR__ . '/Component.isLinkCurrent().asserts.php';
