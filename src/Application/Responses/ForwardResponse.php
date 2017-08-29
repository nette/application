<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Responses;

use Nette;


/**
 * Forwards to new request.
 */
final class ForwardResponse implements Nette\Application\IResponse
{
	use Nette\SmartObject;

	/** @var Nette\Application\Request */
	private $request;


	public function __construct(Nette\Application\Request $request)
	{
		$this->request = $request;
	}


	public function getRequest(): Nette\Application\Request
	{
		return $this->request;
	}


	/**
	 * Sends response to output.
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void
	{
	}
}
