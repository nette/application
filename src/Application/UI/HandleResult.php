<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

/**
 * Wrapper class of a value and origin pair.
 *
 * Used for action and signal result handling.
 *
 * @package Nette\Application\UI
 */
class HandleResult
{
	/** @var mixed */
	private $value;

	/** @var string */
	private $origin;

	/**
	 * HandleResult constructor.
	 *
	 * @param mixed $value
	 * @param string $origin
	 */
	public function __construct($value, string $origin)
	{
		$this->value = $value;
		$this->origin = $origin;
	}

	/** @return mixed */
	public function getValue()
	{
		return $this->value;
	}

	/** @return string */
	public function getOrigin(): string
	{
		return $this->origin;
	}

}
