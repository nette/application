<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationDI;

use Nette;
use function array_filter, array_values, class_exists, count, implode, touch;


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
			$services = array_values(array_filter($services, fn($service) => $this->container->getServiceType($service) === $class));
			if (count($services) > 1) {
				throw new Nette\Application\InvalidPresenterException("Multiple services of type $class found: " . implode(', ', $services) . '.');
			}
		}

		if (count($services) === 1) {
			return $this->container->createService($services[0]);
		}

		if ($this->touchToRefresh && class_exists($class)) {
			touch($this->touchToRefresh);
			echo 'Class ' . htmlspecialchars($class) . ' was not found in DI container.<br><br>If you just created this presenter, it should be enough to refresh the page. It will happen automatically in 5 seconds.<br><br>Otherwise, please check the configuration of your DI container.';
			header('Refresh: 5');
			exit;
		}

		throw new Nette\Application\InvalidPresenterException("No services of type $class found.");
	}
}
