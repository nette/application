<?php

class MockPresenterFactory implements Nette\Application\IPresenterFactory
{
	function getPresenterClass(& $name)
	{
		return str_replace(':', 'Module\\', $name) . 'Presenter';
	}

	function createPresenter($name)
	{}
}
