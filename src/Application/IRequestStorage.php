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
	 * Stores current request and returns key.
	 * @param  Request application request
	 * @param  string expiration time
	 * @return string key
	 */
	function storeRequest(Request $request, $expiration = '+ 10 minutes');


	/**
	 * Restores request by its key.
	 * @param  string key
	 * @return Responses\RedirectResponse|NULL
	 */
	function restoreRequest($key);


	/**
	 * Returns stored request.
	 * @param  \Nette\Http\IRequest
	 * @return Request|NULL
	 */
	function getRequest(Nette\Http\IRequest $httpRequest);

}
