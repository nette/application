<?php

/**
 * Test: Nette\Application\UI\Component::isLinkCurrent()
 */

use Nette\Application;

require __DIR__ . '/../bootstrap.php';

class TestPresenter extends Application\UI\Presenter
{
	public function actionDefault($int, $bool)
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
	public function handleClick($x, $y)
	{
	}


	public function handleOtherSignal()
	{
	}
}

require __DIR__ . '/Component.isLinkCurrent().asserts.php';
