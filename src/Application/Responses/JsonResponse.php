<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\Responses;

use Nette;


/**
 * JSON response used mainly for AJAX requests.
 */
class JsonResponse extends Nette\Object implements Nette\Application\IResponse
{
	/** @var array|\stdClass */
	private $payload;

	/** @var string */
	private $contentType;


	/**
	 * @param  array|\stdClass  payload
	 * @param  string    MIME content type
	 */
	public function __construct($payload, $contentType = NULL)
	{
		if (!is_array($payload) && !is_object($payload)) {
			throw new Nette\InvalidArgumentException(sprintf('Payload must be array or object class, %s given.', gettype($payload)));
		}
		$this->payload = $payload;
		$this->contentType = $contentType ? $contentType : 'application/json';
	}


	/**
	 * @return array|\stdClass
	 */
	public function getPayload()
	{
		return $this->payload;
	}


	/**
	 * Returns the MIME content type of a downloaded file.
	 * @return string
	 */
	public function getContentType()
	{
		return $this->contentType;
	}


	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		$httpResponse->setContentType($this->contentType, 'utf-8');
		echo Nette\Utils\Json::encode($this->payload);
	}

}
