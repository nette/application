<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;


/**
 * Defines Template factory.
 */
interface TemplateFactory
{
	function createTemplate(Control $control = null): Template;
}


interface_exists(ITemplateFactory::class);
