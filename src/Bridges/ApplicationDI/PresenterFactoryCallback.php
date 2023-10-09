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
	private Nette\DI\Container $container;
	private int $invalidLinkMode;
	private ?string $touchToRefresh;


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
			$exact = array_keys(array_map([$this->container, 'getServiceType'], $services), $class, strict: true);
			if (count($exact) === 1) {
				return $this->container->createService($services[$exact[0]]);
			}

			throw new Nette\Application\InvalidPresenterException("Multiple services of type $class found: " . implode(', ', $services) . '.');

		} elseif (!$services) {
			if ($this->touchToRefresh) {
				touch($this->touchToRefresh);
			}

			try {
				$presenter = $this->container->createInstance($class);
				$this->container->callInjects($presenter);
			} catch (Nette\DI\MissingServiceException | Nette\DI\ServiceCreationException $e) {
				if ($this->touchToRefresh && class_exists($class)) {
					throw new \Exception("Refresh your browser. New presenter $class was found.", 0, $e);
				}

				throw $e;
			}

			if ($presenter instanceof Nette\Application\UI\Presenter && !isset($presenter->invalidLinkMode)) {
				$presenter->invalidLinkMode = $this->invalidLinkMode;
			}

			return $presenter;
		}

		return $this->container->createService($services[0]);
	}
}
