<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;

use Nette;


/**
 * Any response returned by presenter.
 */
interface Response
{
	/**
	 * Sends response to output.
	 */
	function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void;
}


interface_exists(IResponse::class);
