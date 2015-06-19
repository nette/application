<?php

/**
 * Test: UIMacros: {control ...}
 */

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$compiler = new Latte\Compiler;
UIMacros::install($compiler);

// {control ...}
Assert::match('<?php %a% $_control->getComponent("form"); %a%->render() ?>',  $compiler->expandMacro('control', 'form', '')->openingCode);
Assert::match('<?php %a% $_control->getComponent("form"); %a%->render(); echo $template->filter(%a%) ?>',  $compiler->expandMacro('control', 'form', 'filter')->openingCode);
Assert::match('<?php if (is_object($form)) %a% else %a% $_control->getComponent($form); %a%->render() ?>',  $compiler->expandMacro('control', '$form', '')->openingCode);
Assert::match('<?php %a% $_control->getComponent("form"); %a%->renderType() ?>',  $compiler->expandMacro('control', 'form:type', '')->openingCode);
Assert::match('<?php %a% $_control->getComponent("form"); %a%->{"render$type"}() ?>',  $compiler->expandMacro('control', 'form:$type', '')->openingCode);
Assert::match('<?php %a% $_control->getComponent("form"); %a%->renderType(\'param\') ?>',  $compiler->expandMacro('control', 'form:type param', '')->openingCode);
Assert::match('<?php %a% $_control->getComponent("form"); %a%->renderType([\'param\' => 123]) ?>',  $compiler->expandMacro('control', 'form:type param => 123', '')->openingCode);
Assert::match('<?php %a% $_control->getComponent("form"); %a%->renderType([\'param\' => 123]) ?>',  $compiler->expandMacro('control', 'form:type, param => 123', '')->openingCode);
Assert::match('<?php %a% $_control->getComponent("form"); %a%->render(); echo $template->striptags(%a%) ?>',  $compiler->expandMacro('control', 'form', 'striptags')->openingCode);
