<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Latte\Runtime\TemplatePrinter as Helpers;
use Nette;
use Nette\Application\UI\Presenter;


/**
 * Generates blueprint of template class.
 */
final class TemplatePrinter
{
	use Nette\SmartObject;

	public function print(Latte\Runtime\Template $template, string $class = null): string
	{
		$types = array_map([Helpers::class, 'getType'], $template->getParameters());
		if ($template->getParameter('control') instanceof Presenter) {
			unset($types['control']);
			$subject = $template->getParameter('presenter');
			$class = $class ?: preg_replace('#Presenter$#', '', get_class($subject)) . ucfirst($subject->getView()) . 'Template';
		} else {
			unset($types['presenter']);
			$subject = $template->getParameter('control');
			$class = $class ?: preg_replace('#Control$#', '', get_class($subject)) . 'Template';
		}
		unset($types['user'], $types['baseUrl'], $types['basePath'], $types['flashes']);

		$funcs = [];
		foreach ($template->global as $name => $val) {
			if (substr($name, 0, 3) === '_fn') {
				$name = substr($name, 3);
				$funcs[$name] = Helpers::printFunction($name, $val, true);
			}
		}
		unset($funcs['islinkcurrent'], $funcs['ismodulecurrent']);

		$parts = explode('\\', $class);
		$name = array_pop($parts);
		$namespace = implode('\\', $parts);
		$parent = Template::class;
		$subject = explode('\\', get_class($subject));
		$subject = end($subject);
		return
			($namespace ? "namespace $namespace;\n\n" : '')
			. "class $name extends $parent\n"
			. "{\n" . Helpers::printProperties($types, true) . "\n}\n"
			. "\n\n"
			. "/**\n" . Helpers::printProperties($types, false) . "\n"
			. implode("\n", $funcs) . "\n */\n"
			. "class $name extends $parent\n"
			. "{\n}\n"
			. "\n\n"
			. "/**\n * @property $name \$template\n */\n"
			. "class $subject\n"
			. "{\n}\n";
	}
}
