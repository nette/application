<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;


/**
 * Component with ability to save and load its state.
 */
interface StatePersistent
{
	/**
	 * Loads state information.
	 */
	function loadState(array $params): void;

	/**
	 * Saves state information for next request.
	 */
	function saveState(array &$params): void;
}


interface_exists(IStatePersistent::class);
