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
use Nette\Application\UI;
use Nette\Bridges\ApplicationLatte\Template;

/**
 * {templatePrint [ClassName]}
 */
class TemplatePrintNode extends Latte\Essential\Nodes\TemplatePrintNode
{
	public function print(PrintContext $context): string
	{
		return self::class . '::printClass($this->getParameters(), ' . PhpHelpers::dump($this->template ?? Template::class) . '); exit;';
	}


	public static function printClass(array $params, string $parentClass): void
	{
		$bp = new Latte\Essential\Blueprint;
		if (!method_exists($bp, 'generateTemplateClass')) {
			throw new \LogicException("Please update 'latte/latte' to version 3.0.15 or newer.");
		}

		$control = $params['control'] ?? $params['presenter'] ?? null;
		$name = 'Template';
		if ($control instanceof UI\Control) {
			$name = preg_replace('#(Control|Presenter)$#', '', $control::class) . 'Template';
			unset($params[$control instanceof UI\Presenter ? 'control' : 'presenter']);
		}
		$class = $bp->generateTemplateClass($params, $name, $parentClass);
		$code = (string) $class->getNamespace();

		$bp->printBegin();
		$bp->printCode($code);

		if ($control instanceof UI\Control) {
			$file = dirname((new \ReflectionClass($control))->getFileName()) . '/' . $class->getName() . '.php';
			if (file_exists($file)) {
				echo "unsaved, file {$bp->clickableFile($file)} already exists";
			} else {
				echo "saved to file {$bp->clickableFile($file)}";
				file_put_contents($file, "<?php\n\ndeclare(strict_types=1);\n\n$code");
			}
		}

		$bp->printEnd();
		exit;
	}
}
