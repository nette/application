<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application\UI;

use Nette;


/**
 * Component multiplier.
 */
final class Multiplier extends Component
{
	/** @var callable */
	private $factory;


	public function __construct(callable $factory)
	{
		$this->factory = $factory;
	}


	protected function createComponent(string $name): Nette\ComponentModel\IComponent
	{
		return ($this->factory)($name, $this);
	}
}
