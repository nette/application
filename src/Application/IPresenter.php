<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;


/**
 * Presenter converts Request to IResponse.
 */
interface IPresenter
{

	/**
	 * @return IResponse
	 */
	function run(Request $request);

}
