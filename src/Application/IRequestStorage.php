<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * Interface for storing and restoring requests.
 *
 * @author     Martin Major
 */
interface IRequestStorage
{

	/**
	 * Stores request and returns key.
	 * @return string key
	 */
	function store(Request $request, Nette\Http\Url $url);

	/**
	 * Restores original URL.
	 * @param  string key
	 * @return string|NULL
	 */
	function getUrl($key);

	/**
	 * Returns stored request.
	 * @return Request|NULL
	 */
	function restore();

}
