<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application\Responses;

use Nette;


/**
 * Callback response.
 */
class CallbackResponse extends Nette\Object implements Nette\Application\IResponse
{
	/** @var callable */
	private $callback;


	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}


	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		call_user_func($this->callback, $httpRequest, $httpResponse);
	}

}
