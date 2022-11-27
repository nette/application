<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte\Nodes;

use Latte;
use Latte\Compiler\PhpHelpers;
use Latte\Compiler\PrintContext;
use Nette;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\PhpGenerator as Php;


/**
 * {templatePrint [ClassName]}
 */
class TemplatePrintNode extends Latte\Essential\Nodes\TemplatePrintNode
{
	public function print(PrintContext $context): string
	{
		return self::class . '::printClass($this, ' . PhpHelpers::dump($this->template) . '); exit;';
	}


	public static function printClass(Latte\Runtime\Template $template, ?string $parent = null): void
	{
		$blueprint = new Latte\Essential\Blueprint;
		$name = 'Template';
		$params = $template->getParameters();
		$control = $params['control'] ?? $params['presenter'] ?? null;
		if ($control) {
			$name = preg_replace('#(Control|Presenter)$#', '', $control::class) . 'Template';
			unset($params[$control instanceof Presenter ? 'control' : 'presenter']);
		}

		if ($parent) {
			if (!class_exists($parent)) {
				$blueprint->printHeader("{templatePrint}: Class '$parent' doesn't exist.");
				return;
			}

			$params = array_diff_key($params, get_class_vars($parent));
		}

		$funcs = array_diff_key((array) $template->global->fn, (new Latte\Essential\CoreExtension)->getFunctions());
		unset($funcs['isLinkCurrent'], $funcs['isModuleCurrent']);

		$namespace = new Php\PhpNamespace(Php\Helpers::extractNamespace($name));
		$class = $namespace->addClass(Php\Helpers::extractShortName($name));
		$class->setExtends($parent ?: Template::class);
		if (!$parent) {
			$class->addTrait(Nette\SmartObject::class);
		}

		$blueprint->addProperties($class, $params);
		$blueprint->addFunctions($class, $funcs);

		$end = $blueprint->printCanvas();
		$blueprint->printCode((string) $namespace);
		echo $end;
	}
}
