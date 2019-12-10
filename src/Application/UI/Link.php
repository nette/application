<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette;


/**
 * Lazy encapsulation of Component::link().
 * Do not instantiate directly, use Component::lazyLink()
 */
final class Link
{
	use Nette\SmartObject;

	/** @var Component */
	private $component;

	/** @var string */
	private $destination;

	/** @var array */
	private $params;


	/**
	 * Link specification.
	 */
	public function __construct(Component $component, string $destination, array $params = [])
	{
		$this->component = $component;
		$this->destination = $destination;
		$this->params = $params;
	}


	/**
	 * Returns link destination.
	 */
	public function getDestination(): string
	{
		return $this->destination;
	}


	/**
	 * Changes link parameter.
	 * @return static
	 */
	public function setParameter(string $key, $value)
	{
		$this->params[$key] = $value;
		return $this;
	}


	/**
	 * Returns link parameter.
	 * @return mixed
	 */
	public function getParameter(string $key)
	{
		return $this->params[$key] ?? null;
	}


	/**
	 * Returns link parameters.
	 */
	public function getParameters(): array
	{
		return $this->params;
	}


	/**
	 * Converts link to URL.
	 */
	public function __toString(): string
	{
		try {
			return $this->component->link($this->destination, $this->params);

		} catch (\Throwable $e) {
			if (func_num_args() || PHP_VERSION_ID >= 70400) {
				throw $e;
			}
			trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
			return '';
		}
	}
}
