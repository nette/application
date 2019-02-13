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
final class PresenterFactoryCallback
{
	/** @var Nette\DI\Container */
	private $container;

	/** @var int */
	private $invalidLinkMode;

	/** @var string|null */
	private $touchToRefresh;


	public function __construct(Nette\DI\Container $container, int $invalidLinkMode, ?string $touchToRefresh)
	{
		$this->container = $container;
		$this->invalidLinkMode = $invalidLinkMode;
		$this->touchToRefresh = $touchToRefresh;
	}


	public function __invoke(string $class): Nette\Application\IPresenter
	{
		$services = $this->container->findByType($class);
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
