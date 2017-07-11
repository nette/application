<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;

use Nette;


/**
 * The bi-directional router.
 */
interface IRouter
{
	/** only matching route */
	public const ONE_WAY = 0b0001;

	/**
	 * Maps HTTP request to a Request object.
	 */
	function match(Nette\Http\IRequest $httpRequest): ?Request;

	/**
	 * Constructs absolute URL from Request object.
	 */
	function constructUrl(Request $appRequest, Nette\Http\Url $refUrl): ?string;
}
