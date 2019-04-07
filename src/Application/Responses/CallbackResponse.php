<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Responses;

use Nette;


/**
 * Callback response.
 */
final class CallbackResponse implements Nette\Application\IResponse
{
	use Nette\SmartObject;

	/** @var callable */
	private $callback;


	/**
	 * @param  callable&callable(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void  $callback
	 */
	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}


	/**
	 * Sends response to output.
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void
	{
		($this->callback)($httpRequest, $httpResponse);
	}
}
