<?php

/**
 * Test: Nette\Application\UI\PresenterComponent::isLinkCurrent()
 * @phpVersion 7
 */

use Nette\Application;
use Nette\Http;

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

require __DIR__ . '/PresenterComponent.isLinkCurrent().asserts.php';
