<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Component multiplier.
 * @template T of Nette\ComponentModel\IComponent
 */
final class Multiplier extends Component
{
	/** @var callable */
	private $factory;


	/**
	 * @param callable(string, self<T>): (T|null)  $factory
	 */
	public function __construct(callable $factory)
	{
		$this->factory = $factory;
	}


	/**
	 * @return T|null
	 */
	protected function createComponent(string $name): ?Nette\ComponentModel\IComponent
	{
		return ($this->factory)($name, $this);
	}
}
