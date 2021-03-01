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

	private Component $component;

	private string $destination;

	private array $params;


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
	 * Returns link component.
	 */
	public function getComponent(): Component
	{
		return $this->component;
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
	 */
	public function setParameter(string $key, $value): static
	{
		$this->params[$key] = $value;
		return $this;
	}


	/**
	 * Returns link parameter.
	 */
	public function getParameter(string $key): mixed
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
	 * Determines whether this links to the current page.
	 */
	public function isLinkCurrent(): bool
	{
		return $this->component->isLinkCurrent($this->destination, $this->params);
	}


	/**
	 * Converts link to URL.
	 */
	public function __toString(): string
	{
		return $this->component->link($this->destination, $this->params);
	}
}
