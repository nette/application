<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Responses;

use Nette;


/**
 * No response.
 */
final class VoidResponse implements Nette\Application\Response
{
	use Nette\SmartObject;

	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void
	{
	}
}
