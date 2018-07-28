<?php

declare(strict_types=1);

class ParamPresenter extends Nette\Application\UI\Presenter
{
	/** @persistent */
	public $bool = true;


	public function actionDefault($a, $b = null, array $c, array $d = null, $e = 1, $f = 1.0, $g = false)
	{
	}
}
