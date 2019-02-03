<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationDI;

use Nette;
use Tracy;


/**
 * Routing extension for Nette DI.
 */
final class RoutingExtension extends Nette\DI\CompilerExtension
{
	public $defaults = [
		'debugger' => null,
		'routes' => [], // of [mask => action]
		'routeClass' => null,
		'cache' => false,
	];

	/** @var bool */
	private $debugMode;


	public function __construct(bool $debugMode = false)
	{
		$this->defaults['debugger'] = interface_exists(Tracy\IBarPanel::class);
		$this->debugMode = $debugMode;
	}


	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$router = $builder->addDefinition($this->prefix('router'))
			->setType(Nette\Routing\Router::class)
			->setFactory(Nette\Application\Routers\RouteList::class)
			->setExported();

		$routeClass = $config['routeClass'] ?: 'Nette\Application\Routers\Route';
		foreach ($config['routes'] as $mask => $action) {
			$router->addSetup('$service[] = new ' . $routeClass . '(?, ?)', [$mask, $action]);
		}

		if ($this->name === 'routing') {
			$builder->addAlias('router', $this->prefix('router'));
		}
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		if ($this->debugMode && $this->config['debugger'] && $application = $builder->getByType(Nette\Application\Application::class)) {
			$builder->getDefinition($application)->addSetup('@Tracy\Bar::addPanel', [
				new Nette\DI\Definitions\Statement(Nette\Bridges\ApplicationTracy\RoutingPanel::class),
			]);
		}
	}


	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		if (!empty($this->config['cache'])) {
			$method = $class->getMethod(Nette\DI\Container::getMethodName($this->prefix('router')));
			try {
				$router = eval($method->getBody());
				if ($router instanceof Nette\Application\Routers\RouteList) {
					$router->warmupCache();
				}
				$s = serialize($router);
			} catch (\Throwable $e) {
				throw new Nette\DI\ServiceCreationException('Unable to cache router due to error: ' . $e->getMessage(), 0, $e);
			}
			$method->setBody('return unserialize(?);', [$s]);
		}
	}
}
