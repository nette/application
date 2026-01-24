<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;


/**
 * Creates template instances for controls and presenters.
 */
interface TemplateFactory
{
	function createTemplate(?Control $control = null): Template;
}


interface_exists(ITemplateFactory::class);
