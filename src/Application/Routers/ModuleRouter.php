<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application\Routers;

use Nette;


/**
 * @author     David Grudl
 * @property-read string $module
 */
class ModuleRouter extends Nette\Object implements Nette\Application\IRouter
{
	/** @var string */
	private $module;

	/** @var Nette\Application\IRouter */
	private $innerRouter;


	/**
	 * @param  string
	 * @param  Nette\Application\IRouter|Nette\Application\IRouter[]
	 */
	public function __construct($module, $innerRouter)
	{
		$this->module = $module ? $module . ':' : '';
		$this->innerRouter = is_array($innerRouter) ? new RouteList($innerRouter) : $innerRouter;
	}


	/**
	 * Maps HTTP request to a Request object.
	 * @return Nette\Application\Request|NULL
	 */
	public function match(Nette\Http\IRequest $httpRequest)
	{
		$appRequest = $this->innerRouter->match($httpRequest);
		if ($this->module && $appRequest !== NULL) {
			$name = $appRequest->getPresenterName();
			if (strncmp($name, 'Nette:', 6)) {
				$appRequest->setPresenterName($this->module . $name);
			}
		}
		return $appRequest;
	}


	/**
	 * Constructs absolute URL from Request object.
	 * @return string|NULL
	 */
	public function constructUrl(Nette\Application\Request $appRequest, Nette\Http\Url $refUrl)
	{
		if ($this->module) {
			$name = $appRequest->getPresenterName();
			if (strncasecmp($name, $this->module, strlen($this->module)) === 0) {
				$appRequest = clone $appRequest;
				$appRequest->setPresenterName(substr($name, strlen($this->module)));
			} else {
				return NULL;
			}
		}
		return $this->innerRouter->constructUrl($appRequest, $refUrl);
	}


	/**
	 * @return string
	 */
	public function getModule()
	{
		return $this->module;
	}

}
