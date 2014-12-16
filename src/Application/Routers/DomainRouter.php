<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application\Routers;

use Nette;


/**
 * @property-read string $host
 */
class DomainRouter extends Nette\Object implements Nette\Application\IRouter
{
	/** @var string */
	private $host;

	/** @var Nette\Application\IRouter */
	private $innerRouter;

	/** @var Nette\Http\Url */
	private $lastRefUrl;

	/** @var Nette\Http\Url */
	private $lastFixedRefUrl;


	/**
	 * @param  string
	 * @param  Nette\Application\IRouter|Nette\Application\IRouter[]
	 */
	public function __construct($host, $innerRouter)
	{
		$this->host = $host;
		$this->innerRouter = is_array($innerRouter) ? new RouteList($innerRouter) : $innerRouter;
	}


	/**
	 * Maps HTTP request to a Request object.
	 * @return Nette\Application\Request|NULL
	 */
	public function match(Nette\Http\IRequest $httpRequest)
	{
		if ($httpRequest->getUrl()->getHost() === $this->host) {
			return $this->innerRouter->match($httpRequest);
		}
		return NULL;
	}


	/**
	 * Constructs absolute URL from Request object.
	 * @return string|NULL
	 */
	public function constructUrl(Nette\Application\Request $appRequest, Nette\Http\Url $refUrl)
	{
		if ($this->lastRefUrl !== $refUrl) {
			$this->lastRefUrl = $refUrl;
			$this->lastFixedRefUrl = clone $refUrl;
			$this->lastFixedRefUrl->setHost($this->host);
		}
		return $this->innerRouter->constructUrl($appRequest, $this->lastFixedRefUrl);
	}


	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

}
