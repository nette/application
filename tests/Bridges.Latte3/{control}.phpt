<?php

/**
 * Test: {control ...}
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(null));

// {control ...}
Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp = $this->global->uiControl->getComponent('form');
				if ($ʟ_tmp instanceof Nette\Application\UI\Renderable) $ʟ_tmp->redrawControl(null, false);
				$ʟ_tmp->render() /* line 1 */;
		%A%
		XX,
	$latte->compile('{control form}'),
);

Assert::match(
	<<<'XX'
		%A%
				if (!is_object($ʟ_tmp = $form)) $ʟ_tmp = $this->global->uiControl->getComponent($ʟ_tmp);
				if ($ʟ_tmp instanceof Nette\Application\UI\Renderable) $ʟ_tmp->redrawControl(null, false);
				$ʟ_tmp->render() /* line 1 */;
		%A%
		XX,
	$latte->compile('{control $form}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp = $this->global->uiControl->getComponent('form');
				%A%
				$ʟ_tmp->renderType() /* line 1 */;
		%A%
		XX,
	$latte->compile('{control form:type}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp = $this->global->uiControl->getComponent('form');
				%A%
				$ʟ_tmp->{'render' . $type}() /* line 1 */;
		%A%
		XX,
	$latte->compile('{control form:$type}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType('param') /* line 1 */;
		%A%
		XX,
	$latte->compile('{control form:type param}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->render(...$params) /* line 1 */;
		%A%
		XX,
	$latte->compile('{control form (expand) $params}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType(['param' => 123]) /* line 1 */;
		%A%
		XX,
	$latte->compile('{control form:type param => 123}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType(param: 123) /* line 1 */;
		%A%
		XX,
	$latte->compile('{control form:type, param: 123}'),
);

Assert::match(
	<<<'XX'
		%A%
				$ʟ_tmp->renderType(param: 123) /* line 1 */;
		%A%
		XX,
	$latte->compile('{control form:type, param: 123}'),
);
