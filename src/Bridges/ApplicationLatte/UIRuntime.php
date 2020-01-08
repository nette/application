<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Nette;
use Nette\Application\UI\Presenter;
use Nette\PhpGenerator as Php;


/**
 * Runtime helpers for UI macros.
 * @internal
 */
final class UIRuntime
{
	use Nette\StaticClass;

	public static function initialize(Latte\Runtime\Template $template, &$parentName, array $blocks): void
	{
		$providers = $template->global;
		$blocks = array_filter(array_keys($blocks), function (string $s): bool { return $s[0] !== '_'; });
		if (
			$parentName === null
			&& $blocks
			&& !$template->getReferringTemplate()
			&& ($providers->uiControl ?? null) instanceof Nette\Application\UI\Presenter
		) {
			$parentName = $providers->uiControl->findLayoutTemplateFile();
		}
	}


	public static function printClass(Latte\Runtime\Template $template, string $parent = null): void
	{
		$name = 'Template';
		$parent = $parent === 'default'
			? DefaultTemplate::class
			: ($parent ?: Template::class);

		$params = $template->getParameters();
		$control = $params['control'] ?? $params['presenter'] ?? null;
		if ($control) {
			$name = preg_replace('#(Control|Presenter)$#', '', get_class($control)) . 'Template';
			unset($params[$control instanceof Presenter ? 'control' : 'presenter']);
		}

		if (class_exists($parent)) {
			get_class_vars($parent);
			$params = array_diff_key($params, get_class_vars($parent));
		}

		$funcs = (array) $template->global->fn;
		unset($funcs['isLinkCurrent'], $funcs['isModuleCurrent']);

		$namespace = new Php\PhpNamespace(Php\Helpers::extractNamespace($name));
		$class = $namespace->addClass(Php\Helpers::extractShortName($name));
		$class->setExtends($parent);
		$class->addTrait(Nette\SmartObject::class);

		$blueprint = new Latte\Runtime\Blueprint;
		$blueprint->addProperties($class, $params, true);
		$blueprint->addFunctions($class, $funcs);

		$end = $blueprint->printCanvas();
		$blueprint->printHeader('Native types');
		$blueprint->printCode((string) $namespace);

		$blueprint->addProperties($class, $params, false);

		$blueprint->printHeader('phpDoc types');
		$blueprint->printCode((string) $namespace);
		echo $end;
	}
}
