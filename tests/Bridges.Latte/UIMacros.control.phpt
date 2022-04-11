<?php

/**
 * Test: UIMacros: {control ...}
 */

declare(strict_types=1);

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
UIMacros::install($latte->getCompiler());

// {control ...}
Assert::match(
	'%A% $this->global->uiControl->getComponent("form");%A%->render();%A%',
	$latte->compile('{control form}')
);

@Assert::match(
	<<<'XX'
%A%
		/* line 1 */ $_tmp = $this->global->uiControl->getComponent("form");
		if ($_tmp instanceof Nette\Application\UI\Renderable) $_tmp->redrawControl(null, false);
		ob_start(function () {});
		$_tmp->render();
		$ʟ_fi = new LR\FilterInfo('html');
		echo $this->filters->filterContent('filter', $ʟ_fi, ob_get_clean());
%A%
XX
	,
	$latte->compile('{control form|filter}')
); // @deprecated

Assert::match(
	<<<'XX'
%A%
		/* line 1 */ if (is_object($form)) $_tmp = $form;
		else $_tmp = $this->global->uiControl->getComponent($form);
		if ($_tmp instanceof Nette\Application\UI\Renderable) $_tmp->redrawControl(null, false);
		$_tmp->render();
%A%
XX
	,
	$latte->compile('{control $form}')
);

Assert::match(
	'%A% $this->global->uiControl->getComponent("form");%A%->renderType();%A%',
	$latte->compile('{control form:type}')
);

Assert::match(
	'%A% $this->global->uiControl->getComponent("form");%A%->{"render$type"}();%A%',
	$latte->compile('{control form:$type}')
);

Assert::match(
	'%A% $this->global->uiControl->getComponent("form");%A%->renderType(\'param\');%A%',
	$latte->compile('{control form:type param}')
);

Assert::match(
	'%A% $this->global->uiControl->getComponent("form");%A%->render(array_merge([], $params, []));%A%',
	$latte->compile('{control form (expand) $params}')
);

Assert::match(
	'%A% $this->global->uiControl->getComponent("form");%A%->renderType([\'param\' => 123]);%A%',
	$latte->compile('{control form:type param => 123}')
);

Assert::match(
	'%A% $this->global->uiControl->getComponent("form");%A%->renderType([\'param\' => 123]);%A%',
	$latte->compile('{control form:type, param => 123}')
);

Assert::match(
	'%A% $this->global->uiControl->getComponent("form");%A%->renderType(param: 123);%A%',
	$latte->compile('{control form:type, param: 123}')
);
