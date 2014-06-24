<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

interface IPresenterMapper
{
	/**
	 * Formats presenter class name from its name.
	 * @param  string
	 * @return string
	 */
	function formatPresenterClass($presenter);

	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string
	 */
	function unformatPresenterClass($class);
}