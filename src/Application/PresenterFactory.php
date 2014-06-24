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
	/** @var bool */
	public $caseSensitive = FALSE;

	/** @var array */
	private $cache = array();

	/** @var Nette\DI\Container */
	private $container;

	/** @var \Nette\Application\IPresenterMapper */
	private $presenterMapper;


	public function __construct(Nette\DI\Container $container, IPresenterMapper $presenterMapper)
	{
		$this->container = $container;
		$this->presenterMapper = $presenterMapper;
	}


	/**
	 * Creates new presenter instance.
	 * @param  string  presenter name
	 * @return IPresenter
	 */
	public function createPresenter($name)
	{
		$class = $this->getPresenterClass($name);
		if (count($services = $this->container->findByType($class)) === 1) {
			$presenter = $this->container->createService($services[0]);
		} else {
			$presenter = $this->container->createInstance($class);
		}
		$this->container->callInjects($presenter);

		if ($presenter instanceof UI\Presenter && $presenter->invalidLinkMode === NULL) {
			$presenter->invalidLinkMode = $this->container->parameters['debugMode'] ? UI\Presenter::INVALID_LINK_WARNING : UI\Presenter::INVALID_LINK_SILENT;
		}
		return $presenter;
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
			list($class, $name) = $this->cache[$name];
			return $class;
		}

		if (!is_string($name) || !Nette\Utils\Strings::match($name, '#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*\z#')) {
			throw new InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
		}

		$class = $this->presenterMapper->formatPresenterClass($name);
		if (!class_exists($class)) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' was not found.");
		}

		$reflection = new Nette\Reflection\ClassType($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
		} elseif ($reflection->isAbstract()) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
		}

		// canonicalize presenter name
		$realName = $this->presenterMapper->unformatPresenterClass($class);
		if ($name !== $realName) {
			if ($this->caseSensitive) {
				throw new InvalidPresenterException("Cannot load presenter '$name', case mismatch. Real name is '$realName'.");
			} else {
				$this->cache[$name] = array($class, $realName);
				$name = $realName;
			}
		} else {
			$this->cache[$name] = array($class, $realName);
		}

		return $class;
	}

}
