<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;


/**
 * Defines template.
 */
interface ITemplate
{

	/**
	 * Renders template to output.
	 * @return void
	 */
	function render();

	/**
	 * Sets the path to the template file.
	 * @return void
	 */
	function setFile($file);

	/**
	 * Returns the path to the template file.
	 * @return string
	 */
	function getFile();

}
