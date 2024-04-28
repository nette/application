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
	public function __construct(
		private readonly Nette\DI\Container $container,
		private readonly int $invalidLinkMode,
		private readonly ?string $touchToRefresh,
	) {
	}


	public function __invoke(string $class): Nette\Application\IPresenter
	{
		$services = $this->container->findByType($class);
		if (count($services) > 1) {
			$services = array_values(array_filter($services, fn($service) => $this->container->getServiceType($service) === $class));
			if (count($services) > 1) {
				throw new Nette\Application\InvalidPresenterException("Multiple services of type $class found: " . implode(', ', $services) . '.');
			}
		}

		if (count($services) === 1) {
			return $this->container->createService($services[0]);
		}

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
}
