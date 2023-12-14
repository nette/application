<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;

use Nette;


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
}
