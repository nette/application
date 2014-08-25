<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationDI;

use Nette;


/**
 * Application extension for Nette DI.
 *
 * @author     David Grudl
 */
class ApplicationExtension extends Nette\DI\CompilerExtension
{
	public $defaults = array(
		'debugger' => TRUE,
		'errorPresenter' => 'Nette:Error',
		'catchExceptions' => NULL,
		'mapping' => NULL
	);


	public function __construct($debugMode = FALSE)
	{
		$this->defaults['catchExceptions'] = !$debugMode;
	}


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$config = $this->compiler->getConfig();
		if ($old = !isset($config[$this->name]) && isset($config['nette']['application'])) {
			$config = Nette\DI\Config\Helpers::merge($config['nette']['application'], $this->defaults);
			// trigger_error("Configuration section 'nette.application' is deprecated, use section '$this->name' instead.", E_USER_DEPRECATED);
		} else {
			$config = $this->getConfig($this->defaults);
		}

		$this->validate($config, $this->defaults, $old ? 'nette.application' : $this->name);

		$application = $container->addDefinition('application') // no namespace for back compatibility
			->setClass('Nette\Application\Application')
			->addSetup('$catchExceptions', array($config['catchExceptions']))
			->addSetup('$errorPresenter', array($config['errorPresenter']));

		if ($config['debugger']) {
			$application->addSetup('Nette\Bridges\ApplicationTracy\RoutingPanel::initializePanel');
		}

		$presenterFactory = $container->addDefinition('nette.presenterFactory')
			->setClass('Nette\Application\IPresenterFactory')
			->setFactory('Nette\Application\PresenterFactory');

		if ($config['mapping']) {
			$presenterFactory->addSetup('setMapping', array($config['mapping']));
		}
	}


	private function validate(array $config, array $expected, $name)
	{
		if ($extra = array_diff_key($config, $expected)) {
			$extra = implode(", $name.", array_keys($extra));
			throw new Nette\InvalidStateException("Unknown option $name.$extra.");
		}
	}

}
