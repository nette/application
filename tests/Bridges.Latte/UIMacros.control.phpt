<?php

/**
 * Test: UIMacros: {control ...}
 */

declare(strict_types=1);

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$compiler = new Latte\Compiler;
UIMacros::install($compiler);

// {control ...}
Assert::match('<?php %a% $this->global->uiControl->getComponent("form"); %a%->render(); ?>', $compiler->expandMacro('control', 'form', '')->openingCode);
@Assert::match('<?php %a% $this->global->uiControl->getComponent("form"); %a%->render(); echo ($this->filters->filter)(%a%); ?>', $compiler->expandMacro('control', 'form', 'filter')->openingCode); // @ deprecated
Assert::match('<?php %a% if (is_object($form)) %a% else %a% $this->global->uiControl->getComponent($form); %a%->render(); ?>', $compiler->expandMacro('control', '$form', '')->openingCode);
Assert::match('<?php %a% $this->global->uiControl->getComponent("form"); %a%->renderType(); ?>', $compiler->expandMacro('control', 'form:type', '')->openingCode);
Assert::match('<?php %a% $this->global->uiControl->getComponent("form"); %a%->{"render$type"}(); ?>', $compiler->expandMacro('control', 'form:$type', '')->openingCode);
Assert::match('<?php %a% $this->global->uiControl->getComponent("form"); %a%->renderType(\'param\'); ?>', $compiler->expandMacro('control', 'form:type param', '')->openingCode);
Assert::match('<?php %a% $this->global->uiControl->getComponent("form"); %a%->render(array_merge([], $params, [])); ?>', $compiler->expandMacro('control', 'form (expand) $params', '')->openingCode);
Assert::match('<?php %a% $this->global->uiControl->getComponent("form"); %a%->renderType([\'param\' => 123]); ?>', $compiler->expandMacro('control', 'form:type param => 123', '')->openingCode);
Assert::match('<?php %a% $this->global->uiControl->getComponent("form"); %a%->renderType([\'param\' => 123]); ?>', $compiler->expandMacro('control', 'form:type, param => 123', '')->openingCode);
@Assert::match('<?php %a% $this->global->uiControl->getComponent("form"); %a%->render(); echo ($this->filters->striptags)(%a%); ?>', $compiler->expandMacro('control', 'form', 'striptags')->openingCode); // @ deprecated
