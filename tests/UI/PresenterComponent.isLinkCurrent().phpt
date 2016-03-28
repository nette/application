<?php

/**
 * Test: Nette\Application\UI\PresenterComponent::isLinkCurrent()
 */

use Nette\Application;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/MockPresenterFactory.php';

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

require __DIR__ . '/PresenterComponent.isLinkCurrent().asserts.php';
