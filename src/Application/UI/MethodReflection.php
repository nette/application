<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;


/**
 * @internal
 */
final class MethodReflection extends \ReflectionMethod
{
	/**
	 * Has method specified annotation?
	 * @deprecated
	 */
	public function hasAnnotation(string $name): bool
	{
		trigger_error(__METHOD__ . '() is deprecated', E_USER_DEPRECATED);
		return (bool) ComponentReflection::parseAnnotation($this, $name);
	}


	/**
	 * Returns an annotation value.
	 * @deprecated
	 */
	public function getAnnotation(string $name): mixed
	{
		trigger_error(__METHOD__ . '() is deprecated', E_USER_DEPRECATED);
		$res = ComponentReflection::parseAnnotation($this, $name);
		return $res ? end($res) : null;
	}
}
