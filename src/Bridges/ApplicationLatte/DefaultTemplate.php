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
 *
 * @method bool isLinkCurrent(string $destination = null, ...$args)
 * @method bool isModuleCurrent(string $module)
 */
#[\AllowDynamicProperties]
final class DefaultTemplate extends Template
{
	public Nette\Application\IPresenter $presenter;
	public Nette\Application\UI\Control $control;
	public Nette\Security\User $user;
	public string $baseUrl;
	public string $basePath;

	/** @var \stdClass[] */
	public array $flashes = [];

	/** @var \stdClass */
	public mixed $config;


	/**
	 * Adds new template parameter.
	 */
	public function add(string $name, $value): static
	{
		if (property_exists($this, $name)) {
			throw new Nette\InvalidStateException("The variable '$name' already exists.");
		}

		$this->$name = $value;
		return $this;
	}


	/**
	 * Sets all parameters.
	 */
	public function setParameters(array $params): static
	{
		return Nette\Utils\Arrays::toObject($params, $this);
	}
}
