<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationDI;

use Nette;
use Nette\DI\Definitions;
use Nette\Schema\Expect;
use Tracy;


/**
 * Routing extension for Nette DI.
 */
final class RoutingExtension extends Nette\DI\CompilerExtension
{
	/** @var bool */
	private $debugMode;


	public function __construct(bool $debugMode = false)
	{
		$this->debugMode = $debugMode;
	}


	public function getConfigSchema(): Nette\Schema\Schema
	{
		return Expect::structure([
			'debugger' => Expect::bool(),
			'routes' => Expect::arrayOf('string'),
			'routeClass' => Expect::string()->deprecated(),
			'cache' => Expect::bool(false),
		]);
	}


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$router = $builder->addDefinition($this->prefix('router'))
			->setType(Nette\Routing\Router::class)
			->setFactory(Nette\Application\Routers\RouteList::class);

		if ($this->config->routeClass) {
			foreach ($this->config->routes as $mask => $action) {
				$router->addSetup('$service[] = new ' . $this->config->routeClass . '(?, ?)', [$mask, $action]);
			}
		} else {
			foreach ($this->config->routes as $mask => $action) {
				$router->addSetup('$service->addRoute(?, ?)', [$mask, $action]);
			}
		}

		if ($this->name === 'routing') {
			$builder->addAlias('router', $this->prefix('router'));
		}
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		if (
			$this->debugMode &&
			($this->config->debugger ?? $builder->getByType(Tracy\Bar::class)) &&
			($name = $builder->getByType(Nette\Application\Application::class)) &&
			($application = $builder->getDefinition($name)) instanceof Definitions\ServiceDefinition
		) {
			$application->addSetup('@Tracy\Bar::addPanel', [
				new Definitions\Statement(Nette\Bridges\ApplicationTracy\RoutingPanel::class),
			]);
		}
	}


	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		if ($this->config->cache) {
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
