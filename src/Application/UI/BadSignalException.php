<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette;


/**
 * Signal exception.
 */
class BadSignalException extends Nette\Application\BadRequestException
{
	protected $code = 403;
}
