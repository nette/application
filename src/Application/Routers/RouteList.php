<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\Routers;

use JetBrains\PhpStorm\Language;
use Nette;


/**
 * The router broker.
 */
class RouteList extends Nette\Routing\RouteList implements Nette\Routing\Router, \ArrayAccess, \Countable, \IteratorAggregate
{
	private const PresenterKey = 'presenter';

	/** @var string|null */
	private $module;


	public function __construct(?string $module = null)
	{
		parent::__construct();
		$this->module = $module ? $module . ':' : null;
	}


	/**
	 * Support for modules.
	 */
	protected function completeParameters(array $params): ?array
	{
		$presenter = $params[self::PresenterKey] ?? null;
		if (is_string($presenter) && strncmp($presenter, 'Nette:', 6)) {
			$params[self::PresenterKey] = $this->module . $presenter;
		}

		return $params;
	}


	/**
	 * Constructs absolute URL from array.
	 */
	public function constructUrl(array $params, Nette\Http\UrlScript $refUrl): ?string
	{
		if ($this->module) {
			if (strncmp($params[self::PresenterKey], $this->module, strlen($this->module)) !== 0) {
				return null;
			}

			$params[self::PresenterKey] = substr($params[self::PresenterKey], strlen($this->module));
		}

		return parent::constructUrl($params, $refUrl);
	}


	/**
	 * @param  array|string|\Closure  $metadata  default values or metadata or callback for NetteModule\MicroPresenter
	 * @return static
	 */
	public function addRoute(
		#[Language('TEXT')]
		string $mask,
		$metadata = [],
		int $flags = 0
	) {
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


	/**
	 * @param  mixed  $index
	 * @param  Nette\Routing\Router  $router
	 */
	public function offsetSet($index, $router): void
	{
		if ($index === null) {
			$this->add($router);
		} else {
			$this->modify($index, $router);
		}
	}


	/**
	 * @param  int  $index
	 * @return mixed
	 * @throws Nette\OutOfRangeException
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($index)
	{
		if (!$this->offsetExists($index)) {
			throw new Nette\OutOfRangeException('Offset invalid or out of range');
		}

		return $this->getRouters()[$index];
	}


	/**
	 * @param  int  $index
	 */
	public function offsetExists($index): bool
	{
		return is_int($index) && $index >= 0 && $index < count($this->getRouters());
	}


	/**
	 * @param  int  $index
	 * @throws Nette\OutOfRangeException
	 */
	public function offsetUnset($index): void
	{
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


interface_exists(Nette\Application\IRouter::class);
