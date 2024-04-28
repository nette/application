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
		private readonly ?string $touchToRefresh,
	) {
	}


	public function __invoke(string $class): Nette\Application\IPresenter
	{
		$services = $this->container->findByType($class);
		if (count($services) > 1) {
			$services = array_keys(array_map($this->container->getServiceType(...), $services), $class, strict: true);
		}

		if (count($services) === 1) {
			return $this->container->createService($services[0]);

		} elseif (count($services) > 1) {
			throw new Nette\Application\InvalidPresenterException("Multiple services of type $class found: " . implode(', ', $services) . '.');

		} elseif ($this->touchToRefresh && class_exists($class)) {
			touch($this->touchToRefresh);
			header('Refresh: 0');
			exit;

		} else {
			throw new Nette\Application\InvalidPresenterException("No services of type $class found.");
		}
	}
}
