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
interface IStatePersistent
{

	/**
	 * Loads state informations.
	 */
	function loadState(array $params): void;

	/**
	 * Saves state informations for next request.
	 */
	function saveState(array &$params): void;
}
