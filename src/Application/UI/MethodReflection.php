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
	#[\Deprecated]
	public function hasAnnotation(string $name): bool
	{
		return (bool) ComponentReflection::parseAnnotation($this, $name);
	}


	#[\Deprecated]
	public function getAnnotation(string $name): mixed
	{
		$res = ComponentReflection::parseAnnotation($this, $name);
		return $res ? end($res) : null;
	}
}
