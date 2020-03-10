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
class RouteList extends Nette\Routing\RouteList implements Nette\Routing\Router, \ArrayAccess, \Countable, \IteratorAggregate
{
	private const PRESENTER_KEY = 'presenter';

	/** @var string|null */
	private $module;


	public function __construct(string $module = null)
	{
		parent::__construct();
		$this->module = $module ? $module . ':' : null;
	}


	/**
	 * Maps HTTP request to an array.
	 */
	public function match(Nette\Http\IRequest $httpRequest): ?array
	{
		$params = parent::match($httpRequest);

		$presenter = $params[self::PRESENTER_KEY] ?? null;
		if (is_string($presenter) && strncmp($presenter, 'Nette:', 6)) {
			$params[self::PRESENTER_KEY] = $this->module . $presenter;
		}
		return $params;
	}


	/**
	 * Constructs absolute URL from array.
	 */
	public function constructUrl(array $params, Nette\Http\UrlScript $refUrl): ?string
	{
		if ($this->module) {
			if (strncmp($params[self::PRESENTER_KEY], $this->module, strlen($this->module)) === 0) {
				$params[self::PRESENTER_KEY] = substr($params[self::PRESENTER_KEY], strlen($this->module));
			} else {
				return null;
			}
		}

		return parent::constructUrl($params, $refUrl);
	}


	/**
	 * @param  string  $mask  e.g. '<presenter>/<action>/<id \d{1,3}>'
	 * @param  array|string|\Closure  $metadata  default values or metadata or callback for NetteModule\MicroPresenter
	 * @return static
	 */
	public function addRoute(string $mask, $metadata = [], int $flags = 0)
	{
		$this->add(new Route($mask, $metadata), $flags);
		return $this;
	}


	/**
	 * @return static
	 */
	public function withModule(string $module)
	{
		$router = new static;
		$router->module = $module . ':';
		$router->parent = $this;
		$this->add($router);
		return $router;
	}


	public function getModule(): ?string
	{
		return $this->module;
	}


	/** @deprecated */
	public function count(): int
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		return count($this->getRouters());
	}


	/** @deprecated */
	public function offsetSet($index, $router): void
	{
		if ($index === null) {
			/*if (get_class($router) === Route::class) {
				trigger_error(__METHOD__ . '() is deprecated, use addRoute(...)', E_USER_DEPRECATED);
			} else {
				trigger_error(__METHOD__ . '() is deprecated, use add(new ' . get_class($router) . '(...)).', E_USER_DEPRECATED);
			}*/
			$this->add($router);
		} else {
			trigger_error(__METHOD__ . '() is deprecated, use modify($index, $route).', E_USER_DEPRECATED);
			$this->modify($index, $router);
		}
	}


	/** @deprecated */
	public function offsetGet($index)
	{
		trigger_error(__METHOD__ . '() is deprecated, use getRouters().', E_USER_DEPRECATED);
		if (!$this->offsetExists($index)) {
			throw new Nette\OutOfRangeException('Offset invalid or out of range');
		}
		return $this->getRouters()[$index];
	}


	/** @deprecated */
	public function offsetExists($index): bool
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		return is_int($index) && $index >= 0 && $index < $this->count();
	}


	/** @deprecated */
	public function offsetUnset($index): void
	{
		trigger_error(__METHOD__ . '() is deprecated, use modify($index, null).', E_USER_DEPRECATED);
		if (!$this->offsetExists($index)) {
			throw new Nette\OutOfRangeException('Offset invalid or out of range');
		}
		$this->modify($index, null);
	}


	/** @deprecated */
	public function getIterator(): \ArrayIterator
	{
		trigger_error(__METHOD__ . '() is deprecated, use getRouters().', E_USER_DEPRECATED);
		return new \ArrayIterator($this->getRouters());
	}
}
