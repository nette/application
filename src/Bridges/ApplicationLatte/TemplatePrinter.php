<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Latte\Runtime\TemplatePrinter as LattePrinter;
use Nette;
use Nette\Application\UI\Presenter;
use Nette\PhpGenerator as Php;


/**
 * Generates blueprint of template class.
 */
final class TemplatePrinter
{
	use Nette\SmartObject;

	public function print(Latte\Runtime\Template $template, string $name = null): Php\PhpNamespace
	{
		$params = $template->getParameters();
		if ($template->getParameter('control') instanceof Presenter) {
			unset($params['control']);
			$subject = $template->getParameter('presenter');
			$name = $name ?: preg_replace('#Presenter$#', '', get_class($subject)) . ucfirst($subject->getView()) . 'Template';
		} else {
			unset($params['presenter']);
			$subject = $template->getParameter('control');
			$name = $name ?: preg_replace('#Control$#', '', get_class($subject)) . 'Template';
		}
		unset($params['user'], $params['baseUrl'], $params['basePath'], $params['flashes']);

		$funcs = $template->global->_fn->getAll();
		unset($funcs['isLinkCurrent'], $funcs['isModuleCurrent'], $funcs['translate']);

		$namespace = new Php\PhpNamespace(Php\Helpers::extractNamespace($name));
		$class = $namespace->addClass(Php\Helpers::extractShortName($name));

		$printer = new LattePrinter;
		$printer->addProperties($class, $params, true);
		$printer->addProperties($class, $params, false);
		$printer->addFunctions($class, $funcs);
		return $namespace;
	}
}
