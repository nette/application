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
final class RedirectResponse implements Nette\Application\Response
{
	public function __construct(
		private readonly string $url,
		private readonly int $httpCode = Http\IResponse::S302_Found,
	) {
	}


	public function getUrl(): string
	{
		return $this->url;
	}


	public function getCode(): int
	{
		return $this->httpCode;
	}


	/**
	 * Sends response to output.
	 */
	public function send(Http\IRequest $httpRequest, Http\IResponse $httpResponse): void
	{
		$httpResponse->redirect($this->url, $this->httpCode);
	}
}
