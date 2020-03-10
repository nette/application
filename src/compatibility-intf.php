<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;

if (false) {
	/** @deprecated use Nette\Routing\Router */
	interface IRouter
	{
	}
} elseif (!interface_exists(IRouter::class)) {
	class_alias(\Nette\Routing\Router::class, IRouter::class);
}
