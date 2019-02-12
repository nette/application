<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Routers;

use Nette;


/**
 * The router broker.
 */
class RouteList extends Nette\Utils\ArrayList implements Nette\Application\IRouter
{
	private const PRESENTER_KEY = 'presenter';

	/** @var array */
	private $cachedRoutes;

	/** @var string|null */
	private $module;


	public function __construct(string $module = null)
	{
		$this->module = $module ? $module . ':' : null;
	}


	/**
	 * Maps HTTP request to an array.
	 */
	public function match(Nette\Http\IRequest $httpRequest): ?array
	{
		foreach ($this as $route) {
			$params = $route->match($httpRequest);
			if ($params !== null) {
				$presenter = $params[self::PRESENTER_KEY] ?? null;
				if (is_string($presenter) && strncmp($presenter, 'Nette:', 6)) {
					$params[self::PRESENTER_KEY] = $this->module . $presenter;
				}
				return $params;
			}
		}
		return null;
	}


	/**
	 * Constructs absolute URL from array.
	 */
	public function constructUrl(array $params, Nette\Http\UrlScript $refUrl): ?string
	{
		if ($this->cachedRoutes === null) {
			$this->warmupCache();
		}

		if ($this->module) {
			if (strncmp($params[self::PRESENTER_KEY], $this->module, strlen($this->module)) === 0) {
				$params[self::PRESENTER_KEY] = substr($params[self::PRESENTER_KEY], strlen($this->module));
			} else {
				return null;
			}
		}

		$presenter = $params[self::PRESENTER_KEY];
		if (!isset($this->cachedRoutes[$presenter])) {
			$presenter = '*';
		}

		foreach ($this->cachedRoutes[$presenter] as $route) {
			$url = $route->constructUrl($params, $refUrl);
			if ($url !== null) {
				return $url;
			}
		}

		return null;
	}


	public function warmupCache(): void
	{
		$routes = [];
		$routes['*'] = [];

		foreach ($this as $route) {
			$presenters = $route instanceof Route && is_array($tmp = $route->getTargetPresenters())
				? $tmp
				: array_keys($routes);

			foreach ($presenters as $presenter) {
				if (!isset($routes[$presenter])) {
					$routes[$presenter] = $routes['*'];
				}
				$routes[$presenter][] = $route;
			}
		}

		$this->cachedRoutes = $routes;
	}


	/**
	 * Adds the router.
	 * @param  mixed  $index
	 * @param  Nette\Application\IRouter  $route
	 */
	public function offsetSet($index, $route): void
	{
		if (!$route instanceof Nette\Application\IRouter) {
			throw new Nette\InvalidArgumentException('Argument must be IRouter descendant.');
		}
		parent::offsetSet($index, $route);
	}


	public function getModule(): ?string
	{
		return $this->module;
	}
}
