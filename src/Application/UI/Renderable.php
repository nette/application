<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;


/**
 * Component with ability to repaint.
 */
interface Renderable
{
	/**
	 * Forces control to repaint.
	 */
	function redrawControl(): void;

	/**
	 * Is required to repaint the control?
	 */
	function isControlInvalid(): bool;
}


interface_exists(IRenderable::class);
