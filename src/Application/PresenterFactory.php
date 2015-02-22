<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * Default presenter loader.
 *
 * @author     David Grudl
 */
class PresenterFactory extends Nette\Object implements IPresenterFactory
{
	/** @deprecated */
	public $caseSensitive = TRUE;

	/** @var array[] of module => splited mask */
	private $mapping = array(
		'*' => array('', '*Module\\', '*Presenter'),
		'Nette' => array('NetteModule\\', '*\\', '*Presenter'),
	);

	/** @var array */
	private $cache = array();

	/** @var Nette\DI\Container */
	private $container;

	/** @var bool */
	private $autoRebuild;

	/** @var int */
	private $invalidLinkMode;


	public function __construct(Nette\DI\Container $container, $autoRebuild = FALSE)
	{
		$this->container = $container;
		$this->autoRebuild = $autoRebuild;
	}


	/**
	 * Creates new presenter instance.
	 * @param  string  presenter name
	 * @return IPresenter
	 */
	public function createPresenter($name)
	{
		$class = $this->getPresenterClass($name);
		$services = array_keys($this->container->findByTag('nette.presenter'), $class);
		if (count($services) > 1) {
			throw new InvalidPresenterException("Multiple services of type $class found: " . implode(', ', $services) . '.');

		} elseif (!$services) {
			if ($this->autoRebuild) {
				$rc = new \ReflectionClass($this->container);
				@unlink($rc->getFileName()); // @ file may not exists
			}

			$presenter = $this->container->createInstance($class);
			$this->container->callInjects($presenter);
			if ($presenter instanceof UI\Presenter && $presenter->invalidLinkMode === NULL) {
				$presenter->invalidLinkMode = $this->invalidLinkMode;
			}
			return $presenter;
		}

		return $this->container->createService($services[0]);
	}


	/**
	 * Generates and checks presenter class name.
	 * @param  string  presenter name
	 * @return string  class name
	 * @throws InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (isset($this->cache[$name])) {
			return $this->cache[$name];
		}

		if (!is_string($name) || !Nette\Utils\Strings::match($name, '#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*\z#')) {
			throw new InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
		}

		$class = $this->formatPresenterClass($name);
		if (!class_exists($class)) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' was not found.");
		}

		$reflection = new \ReflectionClass($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
		} elseif ($reflection->isAbstract()) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
		}

		$this->cache[$name] = $class;

		if ($name !== ($realName = $this->unformatPresenterClass($class))) {
			trigger_error("Case mismatch on presenter name '$name', correct name is '$realName'.", E_USER_WARNING);
			$name = $realName;
		}

		return $class;
	}


	/**
	 * Sets mapping as pairs [module => mask]
	 * @return self
	 */
	public function setMapping(array $mapping)
	{
		foreach ($mapping as $module => $mask) {
			if (!preg_match('#^\\\\?([\w\\\\]*\\\\)?(\w*\*\w*?\\\\)?([\w\\\\]*\*\w*)\z#', $mask, $m)) {
				throw new Nette\InvalidStateException("Invalid mapping mask '$mask'.");
			}
			$this->mapping[$module] = array($m[1], $m[2] ?: '*Module\\', $m[3]);
		}
		return $this;
	}


	/**
	 * Sets invalid link mode.
	 * @param  int
	 * @return self
	 */
	public function setInvalidLinkMode($invalidLinkMode)
	{
		$this->invalidLinkMode = $invalidLinkMode;
		return $this;
	}


	/**
	 * Formats presenter class name from its name.
	 * @param  string
	 * @return string
	 * @internal
	 */
	public function formatPresenterClass($presenter)
	{
		$parts = explode(':', $presenter);
		$mapping = isset($parts[1], $this->mapping[$parts[0]])
			? $this->mapping[array_shift($parts)]
			: $this->mapping['*'];

		while ($part = array_shift($parts)) {
			$mapping[0] .= str_replace('*', $part, $mapping[$parts ? 1 : 2]);
		}
		return $mapping[0];
	}


	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string
	 * @internal
	 */
	public function unformatPresenterClass($class)
	{
		foreach ($this->mapping as $module => $mapping) {
			$mapping = str_replace(array('\\', '*'), array('\\\\', '(\w+)'), $mapping);
			if (preg_match("#^\\\\?$mapping[0]((?:$mapping[1])*)$mapping[2]\\z#i", $class, $matches)) {
				return ($module === '*' ? '' : $module . ':')
					. preg_replace("#$mapping[1]#iA", '$1:', $matches[1]) . $matches[3];
			}
		}
	}

}
