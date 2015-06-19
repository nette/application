<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationLatte;

use Nette;
use Latte;


interface ILatteFactory
{

	/**
	 * @return Latte\Engine
	 */
	function create();

}
