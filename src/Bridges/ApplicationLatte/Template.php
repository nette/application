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
 *
 * @property Nette\Security\User $user
 * @property string $baseUrl
 * @property string $basePath
 * @property array $flashes
 * @property Nette\Application\UI\Presenter $presenter
 * @property Nette\Application\UI\Control $control
 * @method bool isLinkCurrent(string $destination = null, ...$args)
 * @method bool isModuleCurrent(string $module)
 */
class Template extends LatteTemplate
{
	/**
	 * Adds new template parameter.
	 * @return static
	 */
	public function add(string $name, $value)
	{
		if (property_exists($this, $name)) {
			throw new Nette\InvalidStateException("The variable '$name' already exists.");
		}
		$this->$name = $value;
		return $this;
	}


	/**
	 * Sets all parameters.
	 * @return static
	 */
	public function setParameters(array $params)
	{
		foreach ($params as $k => $v) {
			$this->$k = $v;
		}
		return $this;
	}


	/**
	 * Returns array of all parameters.
	 */
	public function getParameters(): array
	{
		return get_object_vars($this);
	}
}
