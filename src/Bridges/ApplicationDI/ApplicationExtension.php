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
		$config = $this->validateConfig($this->defaults);
		$container = $this->getContainerBuilder();

		$application = $container->addDefinition($this->prefix('application'))
			->setClass('Nette\Application\Application')
			->addSetup('$catchExceptions', array($config['catchExceptions']))
			->addSetup('$errorPresenter', array($config['errorPresenter']));

		if ($config['debugger']) {
			$application->addSetup('Nette\Bridges\ApplicationTracy\RoutingPanel::initializePanel');
		}

		$presenterFactory = $container->addDefinition($this->prefix('presenterFactory'))
			->setClass('Nette\Application\IPresenterFactory')
			->setFactory('Nette\Application\PresenterFactory');

		if ($config['mapping']) {
			$presenterFactory->addSetup('setMapping', array($config['mapping']));
		}

		$container->addDefinition($this->prefix('linkGenerator'))
			->setFactory('Nette\Application\LinkGenerator', array(
				1 => new Nette\DI\Statement('@Nette\Http\Request::getUrl'),
			));

		if ($this->name === 'application') {
			$container->addAlias('application', $this->prefix('application'));
			$container->addAlias('nette.presenterFactory', $this->prefix('presenterFactory'));
		}
	}

}
