<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Responses;

use Nette;
use Nette\Http;


/**
 * Redirects to new URI.
 */
class RedirectResponse implements Nette\Application\IResponse
{
	use Nette\SmartObject;

	/** @var string */
	private $url;

	/** @var int */
	private $code;


	/**
	 * @param  string  URI
	 * @param  int     HTTP code 3xx
	 */
	public function __construct(string $url, int $code = Http\IResponse::S302_FOUND)
	{
		$this->url = $url;
		$this->code = $code;
	}


	public function getUrl(): string
	{
		return $this->url;
	}


	public function getCode(): int
	{
		return $this->code;
	}


	/**
	 * Sends response to output.
	 */
	public function send(Http\IRequest $httpRequest, Http\IResponse $httpResponse): void
	{
		$httpResponse->redirect($this->url, $this->code);
	}

}
