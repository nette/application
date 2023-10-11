<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;


interface LatteFactory
{
	function create(/*?Control $control = null*/): Latte\Engine;
}


interface_exists(ILatteFactory::class);
