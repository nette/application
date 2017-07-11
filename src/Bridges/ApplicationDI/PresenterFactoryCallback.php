<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationDI;

use Nette;


/**
 * PresenterFactory callback.
 * @internal
 */
class PresenterFactoryCallback
{
	/** @var Nette\DI\Container */
	private $container;

	/** @var int */
	private $invalidLinkMode;

	/** @var string|null */
	private $touchToRefresh;


	public function __construct(Nette\DI\Container $container, $invalidLinkMode, $touchToRefresh)
	{
		$this->container = $container;
		$this->invalidLinkMode = $invalidLinkMode;
		$this->touchToRefresh = $touchToRefresh;
	}


	public function __invoke($class): Nette\Application\IPresenter
	{
		$services = array_keys($this->container->findByTag('nette.presenter'), $class);
		if (count($services) > 1) {
			throw new Nette\Application\InvalidPresenterException("Multiple services of type $class found: " . implode(', ', $services) . '.');

		} elseif (!$services) {
			if ($this->touchToRefresh) {
				touch($this->touchToRefresh);
			}

			$presenter = $this->container->createInstance($class);
			$this->container->callInjects($presenter);
			if ($presenter instanceof Nette\Application\UI\Presenter && $presenter->invalidLinkMode === null) {
				$presenter->invalidLinkMode = $this->invalidLinkMode;
			}
			return $presenter;
		}

		return $this->container->createService($services[0]);
	}
}
