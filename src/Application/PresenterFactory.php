<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Application;

use Nette;


/**
 * Default presenter loader.
 */
class PresenterFactory implements IPresenterFactory
{
	/** @var array[] of module => splited mask */
	private array $mapping = [
		'*' => ['', '*Module\\', '*Presenter'],
		'Nette' => ['NetteModule\\', '*\\', '*Presenter'],
	];

	private array $cache = [];

	/** @var callable */
	private $factory;


	/**
	 * @param  ?callable(string): IPresenter  $factory
	 */
	public function __construct(?callable $factory = null)
	{
		$this->factory = $factory ?: fn(string $class): IPresenter => new $class;
	}


	/**
	 * Creates new presenter instance.
	 */
	public function createPresenter(string $name): IPresenter
	{
		return ($this->factory)($this->getPresenterClass($name));
	}


	/**
	 * Generates and checks presenter class name.
	 * @throws InvalidPresenterException
	 */
	public function getPresenterClass(string &$name): string
	{
		if (isset($this->cache[$name])) {
			return $this->cache[$name];
		}

		$class = $this->formatPresenterClass($name);
		if (!class_exists($class)) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' was not found.");
		}

		$reflection = new \ReflectionClass($class);
		$class = $reflection->getName();
		if (!$reflection->implementsInterface(IPresenter::class)) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
		} elseif ($reflection->isAbstract()) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
		}

		return $this->cache[$name] = $class;
	}


	/**
	 * Sets mapping as pairs [module => mask]
	 */
	public function setMapping(array $mapping): static
	{
		foreach ($mapping as $module => $mask) {
			if (is_string($mask)) {
				if (!preg_match('#^\\\\?([\w\\\\]*\\\\)?(\w*\*\w*?\\\\)?([\w\\\\]*\*\*?\w*)$#D', $mask, $m)) {
					throw new Nette\InvalidStateException("Invalid mapping mask '$mask'.");
				}

				$this->mapping[$module] = [$m[1], $m[2] ?: '*Module\\', $m[3]];
			} elseif (is_array($mask) && count($mask) === 3) {
				$this->mapping[$module] = [$mask[0] ? $mask[0] . '\\' : '', $mask[1] . '\\', $mask[2]];
			} else {
				throw new Nette\InvalidStateException("Invalid mapping mask for module $module.");
			}
		}

		return $this;
	}


	/**
	 * Formats presenter class name from its name.
	 * @internal
	 */
	public function formatPresenterClass(string $presenter): string
	{
		if (!Nette\Utils\Strings::match($presenter, '#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#D')) {
			throw new InvalidPresenterException("Presenter name must be alphanumeric string, '$presenter' is invalid.");
		}
		$parts = explode(':', $presenter);
		$mapping = isset($parts[1], $this->mapping[$parts[0]])
			? $this->mapping[array_shift($parts)]
			: $this->mapping['*'];

		while ($part = array_shift($parts)) {
			$mapping[0] .= strtr($mapping[$parts ? 1 : 2], ['**' => "$part\\$part", '*' => $part]);
		}

		return $mapping[0];
	}
}
