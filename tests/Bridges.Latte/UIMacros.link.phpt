<?php

/**
 * Test: UIMacros: {link ...}
 */

declare(strict_types=1);

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$compiler = new Latte\Compiler;
$compiler->setContentType($compiler::CONTENT_TEXT);
UIMacros::install($compiler);

// {link ...}
Assert::same('<?php echo $this->global->uiControl->link("p"); ?>', $compiler->expandMacro('link', 'p', '')->openingCode);
Assert::same('<?php echo ($this->filters->filter)($this->global->uiControl->link("p")); ?>', $compiler->expandMacro('link', 'p', 'filter')->openingCode);
Assert::same('<?php echo $this->global->uiControl->link("p:a"); ?>', $compiler->expandMacro('link', 'p:a', '')->openingCode);
Assert::same('<?php echo $this->global->uiControl->link($dest); ?>', $compiler->expandMacro('link', '$dest', '')->openingCode);
Assert::same('<?php echo $this->global->uiControl->link($p:$a); ?>', $compiler->expandMacro('link', '$p:$a', '')->openingCode);
Assert::same('<?php echo $this->global->uiControl->link("$p:$a"); ?>', $compiler->expandMacro('link', '"$p:$a"', '')->openingCode);
Assert::same('<?php echo $this->global->uiControl->link("p:a"); ?>', $compiler->expandMacro('link', '"p:a"', '')->openingCode);
Assert::same('<?php echo $this->global->uiControl->link(\'p:a\'); ?>', $compiler->expandMacro('link', "'p:a'", '')->openingCode);

Assert::same('<?php echo $this->global->uiControl->link("p", [\'param\']); ?>', $compiler->expandMacro('link', 'p param', '')->openingCode);
Assert::same('<?php echo $this->global->uiControl->link("p", [\'param\' => 123]); ?>', $compiler->expandMacro('link', 'p param => 123', '')->openingCode);
Assert::same('<?php echo $this->global->uiControl->link("p", [\'param\' => 123]); ?>', $compiler->expandMacro('link', 'p, param => 123', '')->openingCode);
