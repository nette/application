<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationDI;

use Nette;


/**
 * Routing extension for Nette DI.
 *
 * @author     David Grudl
 */
class RoutingExtension extends Nette\DI\CompilerExtension
{
	public $defaults = array(
		'debugger' => TRUE,
		'routes' => array(), // of [mask => action]
		'cache' => FALSE,
	);

	/** @var bool */
	private $debugMode;


	public function __construct($debugMode = FALSE)
	{
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

		if ($this->debugMode && $config['debugger'] && $container->hasDefinition('application')) {
			$container->getDefinition('application')->addSetup('@Tracy\Bar::addPanel', array(
				new Nette\DI\Statement('Nette\Bridges\ApplicationTracy\RoutingPanel')
			));
		}

		if ($this->name === 'routing') {
			$container->addAlias('router', $this->prefix('router'));
		}
	}


	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$config = $this->getConfig();
		if (!empty($config['cache'])) {
			$method = $class->methods[Nette\DI\Container::getMethodName($this->prefix('router'))];
			$router = eval($method->body);
			$method->setBody('return unserialize(?);', [serialize($router)]);
		}
	}

}
