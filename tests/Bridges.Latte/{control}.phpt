<?php

/**
 * Test: {control ...}
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(null));

// {control ...}
Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp = $this->global->uiControl->getComponent('form');
				if ($ʟ_tmp instanceof Nette\Application\UI\Renderable) $ʟ_tmp->redrawControl(null, false);
				$ʟ_tmp->render() /* %a% */;
		%A%
		XX,
	$latte->compile('{control form}'),
);

Assert::match(
	<<<'XX'
		%A%
				if (!is_object($ʟ_tmp = $form)) $ʟ_tmp = $this->global->uiControl->getComponent($ʟ_tmp);
				if ($ʟ_tmp instanceof Nette\Application\UI\Renderable) $ʟ_tmp->redrawControl(null, false);
				$ʟ_tmp->render() /* %a% */;
		%A%
		XX,
	$latte->compile('{control $form}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp = $this->global->uiControl->getComponent('form');
				%A%
				$ʟ_tmp->renderType() /* %a% */;
		%A%
		XX,
	$latte->compile('{control form:type}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp = $this->global->uiControl->getComponent('form');
				%A%
				$ʟ_tmp->{'render' . $type}() /* %a% */;
		%A%
		XX,
	$latte->compile('{control form:$type}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType('param') /* %a% */;
		%A%
		XX,
	$latte->compile('{control form:type param}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->render(...$params) /* %a% */;
		%A%
		XX,
	$latte->compile('{control form (expand) $params}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType(['param' => 123]) /* %a% */;
		%A%
		XX,
	$latte->compile('{control form:type param => 123}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType(param: 123) /* %a% */;
		%A%
		XX,
	$latte->compile('{control form:type, param: 123}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType(param: 123) /* %a% */;
		%A%
		XX,
	$latte->compile('{control form:type, param: 123}'),
);
