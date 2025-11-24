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
				$ʟ_tmp->render() /* pos 1:1 */;
		%A%
		XX,
	$latte->compile('{control form}'),
);

Assert::match(
	<<<'XX'
		%A%
				if (!is_object($ʟ_tmp = $form)) $ʟ_tmp = $this->global->uiControl->getComponent($ʟ_tmp);
				if ($ʟ_tmp instanceof Nette\Application\UI\Renderable) $ʟ_tmp->redrawControl(null, false);
				$ʟ_tmp->render() /* pos 1:1 */;
		%A%
		XX,
	$latte->compile('{control $form}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType() /* pos 1:1 */;
		%A%
		XX,
	$latte->compile('{control form:type}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->{'render' . $type}() /* pos 1:1 */;
		%A%
		XX,
	$latte->compile('{control form:$type}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType('param') /* pos 1:1 */;
		%A%
		XX,
	$latte->compile('{control form:type param}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->render(...$params) /* pos 1:1 */;
		%A%
		XX,
	$latte->compile('{control form (expand) $params}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType(['param' => 123]) /* pos 1:1 */;
		%A%
		XX,
	$latte->compile('{control form:type param => 123}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType(param: 123) /* pos 1:1 */;
		%A%
		XX,
	$latte->compile('{control form:type, param: 123}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType(param: 123) /* pos 1:1 */;
		%A%
		XX,
	$latte->compile('{control form:type, param: 123}'),
);
