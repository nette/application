<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;
use function class_parents, class_uses, strrpos, substr;


/**
 * Application helpers.
 */
final class Helpers
{
	use Nette\StaticClass;

	/**
	 * Splits name into [module, presenter] or [presenter, action]
	 * @return array{string, string, string}
	 */
	public static function splitName(string $name): array
	{
		$pos = strrpos($name, ':');
		return $pos === false
			? ['', $name, '']
			: [substr($name, 0, $pos), substr($name, $pos + 1), ':'];
	}


	/**
	 * Returns all classes, parent classes, and traits used by the given class, keyed by name.
	 * @return array<string, class-string>
	 */
	public static function getClassesAndTraits(string $class): array
	{
		$res = [$class => $class] + class_parents($class);
		$addTraits = function (string $type) use (&$res, &$addTraits): void {
			$res += class_uses($type);
			foreach (class_uses($type) as $trait) {
				$addTraits($trait);
			}
		};
		foreach ($res as $type) {
			$addTraits($type);
		}

		return $res;
	}
}
