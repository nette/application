<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;

use Nette\Http;


/**
 * A silent exception used to terminate the current presenter or application.
 * Contains no error message or code.
 */
class AbortException extends \LogicException
{
}


/** @internal */
final class SwitchException extends AbortException
{
}


/**
 * Fatal error in the application.
 */
class ApplicationException extends \Exception
{
}


/**
 * The requested presenter cannot be loaded.
 */
class InvalidPresenterException extends \Exception
{
}


/**
 * The request resulted in HTTP 4xx client error.
 */
class BadRequestException extends \LogicException
{
	/** @var int */
	protected $code = Http\IResponse::S404_NotFound;


	public function __construct(string $message = '', int $httpCode = 0, ?\Throwable $previous = null)
	{
		parent::__construct($message, $httpCode ?: $this->code, $previous);
	}


	public function getHttpCode(): int
	{
		return $this->code;
	}
}


/**
 * Access to the requested resource is forbidden.
 */
class ForbiddenRequestException extends BadRequestException
{
	/** @var int */
	protected $code = Http\IResponse::S403_Forbidden;
}
