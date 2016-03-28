<?php

use Nette\Application;

class TestControl extends Application\UI\Control
{
	/** @persistent array */
	public $order = [];

	/** @persistent int */
	public $round = 0;


	public function handleClick($x, $y)
	{
	}

	public function handleOtherSignal()
	{
	}

	public function loadState(array $params)
	{
		if (isset($params['order'])) {
			$params['order'] = explode('.', $params['order']);
		}
		parent::loadState($params);
	}

	public function saveState(array & $params)
	{
		parent::saveState($params);
		if (isset($params['order'])) {
			$params['order'] = implode('.', $params['order']);
		}
	}

}
