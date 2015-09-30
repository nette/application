<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * Routes collection.
 */
interface IRouteList extends \IteratorAggregate
{
	/**
	 * @return string
	 */
	function getModule();

}
