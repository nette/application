<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Responses;

use Nette;


/**
 * JSON response used mainly for AJAX requests.
 */
final class JsonResponse implements Nette\Application\Response
{
	use Nette\SmartObject;

	private mixed $payload;
	private string $contentType;


	public function __construct($payload, ?string $contentType = null)
	{
		$this->payload = $payload;
		$this->contentType = $contentType ?: 'application/json';
	}


	/**
	 * @return mixed
	 */
	public function getPayload()
	{
		return $this->payload;
	}


	/**
	 * Returns the MIME content type of a downloaded file.
	 */
	public function getContentType(): string
	{
		return $this->contentType;
	}


	/**
	 * Sends response to output.
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void
	{
		$httpResponse->setContentType($this->contentType, 'utf-8');
		echo Nette\Utils\Json::encode($this->payload);
	}
}
