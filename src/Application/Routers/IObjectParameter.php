<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application\Routers;


interface IObjectParameter
{

	/**
	 * @return string representation of the parameter
	 */
	function __toString();

}
