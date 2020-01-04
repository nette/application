<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Nette;


/**
 * Dynamic Latte powered template.
 */
class Template extends LatteTemplate
{
	/** @var array */
	private $params = [];


	/**
	 * Adds new template parameter.
	 * @return static
	 */
	public function add(string $name, $value)
	{
		if (array_key_exists($name, $this->params)) {
			throw new Nette\InvalidStateException("The variable '$name' already exists.");
		}
		$this->params[$name] = $value;
		return $this;
	}


	/**
	 * Sets all parameters.
	 * @return static
	 */
	public function setParameters(array $params)
	{
		$this->params = $params + $this->params;
		return $this;
	}


	/**
	 * Returns array of all parameters.
	 */
	public function getParameters(): array
	{
		return $this->params;
	}


	/**
	 * Sets a template parameter. Do not call directly.
	 */
	public function __set(string $name, $value): void
	{
		$this->params[$name] = $value;
	}


	/**
	 * Returns a template parameter. Do not call directly.
	 * @return mixed  value
	 */
	public function &__get(string $name)
	{
		if (!array_key_exists($name, $this->params)) {
			trigger_error("The variable '$name' does not exist in template.", E_USER_NOTICE);
		}

		return $this->params[$name];
	}


	/**
	 * Determines whether parameter is defined. Do not call directly.
	 */
	public function __isset(string $name): bool
	{
		return isset($this->params[$name]);
	}


	/**
	 * Removes a template parameter. Do not call directly.
	 */
	public function __unset(string $name): void
	{
		unset($this->params[$name]);
	}
}
