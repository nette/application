<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette;
use Nette\Reflection\Method;


/**
 * @internal
 */
class MethodReflection extends \ReflectionMethod
{
	use Nette\SmartObject;

	/**
	 * Has method specified annotation?
	 * @param  string
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		return (bool) ComponentReflection::parseAnnotation($this, $name);
	}


	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return string|NULL
	 */
	public function getAnnotation($name)
	{
		$res = ComponentReflection::parseAnnotation($this, $name);
		return $res ? end($res) : NULL;
	}

}
