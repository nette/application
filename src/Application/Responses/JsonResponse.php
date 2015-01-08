<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application\Responses;

use Nette;


/**
 * JSON response used mainly for AJAX requests.
 *
 * @author     David Grudl
 *
 * @property-read array|\stdClass $payload
 * @property-read string $contentType
 */
class JsonResponse extends Nette\Object implements Nette\Application\IResponse
{
	/** @var array|\stdClass */
	private $payload;

	/** @var string */
	private $contentType;

	/** @var string */
	private $charset;


	/**
	 * @param  array|\stdClass  payload
	 * @param  string           MIME content type
	 * @param  string           charset
	 */
	public function __construct($payload, $contentType = NULL, $charset = NULL)
	{
		if (!is_array($payload) && !is_object($payload)) {
			throw new Nette\InvalidArgumentException(sprintf('Payload must be array or object class, %s given.', gettype($payload)));
		}
		$this->payload = $payload;
		$this->contentType = $contentType ? $contentType : 'application/json';
		$this->charset = $charset;
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
		$httpResponse->setContentType($this->contentType, $this->charset);
		$httpResponse->setExpiration(FALSE);
		echo Nette\Utils\Json::encode($this->payload);
	}

}
