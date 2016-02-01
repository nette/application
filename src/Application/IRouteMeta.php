<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application;


/**
 * Define API for RoutingPanel.
 */
interface IRouteMeta
{

	/**
	 * Return route mask.
	 * @return string
	 */
	function getMask();

	/**
	 * Returns default values.
	 * @return array
	 */
	function getDefaults();

}
