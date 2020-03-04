<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Nette;


/**
 * Default template for controls and presenters.
 */
final class DefaultTemplate extends Template
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
		return Nette\Utils\Arrays::toObject($params, $this);
	}
}
