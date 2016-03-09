<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationDI;

use Nette;


/**
 * Routing extension for Nette DI.
 */
class RoutingExtension extends Nette\DI\CompilerExtension
{
	public $defaults = array(
		'debugger' => NULL,
		'routes' => array(), // of [mask => action]
		'cache' => FALSE,
	);

	/** @var bool */
	private $debugMode;


	public function __construct($debugMode = FALSE)
	{
		$this->defaults['debugger'] = interface_exists('Tracy\IBarPanel');
		$this->debugMode = $debugMode;
	}


	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults);
		$container = $this->getContainerBuilder();

		$router = $container->addDefinition($this->prefix('router'))
			->setClass('Nette\Application\IRouter')
			->setFactory('Nette\Application\Routers\RouteList');

		foreach ($config['routes'] as $mask => $action) {
			$router->addSetup('$service[] = new Nette\Application\Routers\Route(?, ?);', array($mask, $action));
		}

		if ($this->name === 'routing') {
			$container->addAlias('router', $this->prefix('router'));
		}
	}


	public function beforeCompile()
	{
		$container = $this->getContainerBuilder();

		if ($this->debugMode && $this->config['debugger'] && $application = $container->getByType('Nette\Application\Application')) {
			$container->getDefinition($application)->addSetup('@Tracy\Bar::addPanel', array(
				new Nette\DI\Statement('Nette\Bridges\ApplicationTracy\RoutingPanel'),
			));
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
			} catch (\Exception $e) {
				throw new Nette\DI\ServiceCreationException('Unable to cache router due to error: ' . $e->getMessage(), 0, $e);
			}
			$method->setBody('return unserialize(?);', array($s));
		}
	}

}
