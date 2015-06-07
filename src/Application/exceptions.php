<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * The exception that is thrown when user attempts to terminate the current presenter or application.
 * This is special "silent exception" with no error message or code.
 */
class AbortException extends \Exception
{
}


/**
 * Application fatal error.
 */
class ApplicationException extends \Exception
{
}


/**
 * The exception that is thrown when a presenter cannot be loaded.
 */
class InvalidPresenterException extends \Exception
{
}


/**
 * The exception that indicates client error with HTTP code 4xx.
 */
class BadRequestException extends \Exception
{
	/** @var int */
	protected $code = 404;


	/** @var Request */
	private $request;


	public function __construct($message = '', $code = 0, \Exception $previous = NULL, Request $request = NULL)
	{
		parent::__construct($message, $code < 200 || $code > 504 ? $this->code : $code, $previous);
		$this->request = $request;
	}


	/**
	 * @return Request|null
	 */
	public function getRequest()
	{
		return $this->request;
	}


	public static function fromRequest(Request $request, $message = '', $code = 0, \Exception $previous = NULL)
	{
		return new static($message, $code, $previous, $request);
	}

}


/**
 * Forbidden request exception - access denied.
 */
class ForbiddenRequestException extends BadRequestException
{
	/** @var int */
	protected $code = 403;

}
