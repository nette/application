<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\Responses;

use Nette;


/**
 * Callback response.
 */
final class CallbackResponse implements Nette\Application\Response
{
	/** @var callable */
	private $callback;


	/**
	 * @param  callable(Nette\Http\IRequest, Nette\Http\Response): void  $callback
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
