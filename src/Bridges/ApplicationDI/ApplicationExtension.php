<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationDI;

use Nette,
	Nette\Application\UI;


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
		'mapping' => NULL,
		'scanDirs' => array(),
		'scanComposer' => NULL,
		'scanFilter' => 'Presenter',
	);

	/** @var bool */
	private $debugMode;


	public function __construct($debugMode = FALSE, array $scanDirs = NULL)
	{
		$this->defaults['scanDirs'] = (array) $scanDirs;
		$this->defaults['scanComposer'] = class_exists('Composer\Autoload\ClassLoader');
		$this->defaults['catchExceptions'] = !$debugMode;
		$this->debugMode = $debugMode;
	}


	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults);
		$container = $this->getContainerBuilder();
		$container->addExcludedClasses(array('Nette\Application\UI\Control'));

		$application = $container->addDefinition($this->prefix('application'))
			->setClass('Nette\Application\Application')
			->addSetup('$catchExceptions', array($config['catchExceptions']))
			->addSetup('$errorPresenter', array($config['errorPresenter']));

		if ($config['debugger']) {
			$application->addSetup('Nette\Bridges\ApplicationTracy\RoutingPanel::initializePanel');
		}

		$presenterFactory = $container->addDefinition($this->prefix('presenterFactory'))
			->setClass('Nette\Application\IPresenterFactory')
			->setFactory('Nette\Application\PresenterFactory', array(1 => $this->debugMode));

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


	public function beforeCompile()
	{
		$container = $this->getContainerBuilder();
		$all = array();

		foreach ($container->findByType('Nette\Application\IPresenter', FALSE) as $name) {
			$def = $container->getDefinition($name);
			$all[strtolower($def->getClass())] = $def;
		}

		$counter = 0;
		foreach ($this->findPresenters() as $class) {
			if (empty($all[$tmp = strtolower($class)])) {
				$all[$tmp] = $container->addDefinition($this->prefix(++$counter))->setClass($class);
			}
		}

		foreach ($all as $def) {
			$def->setInject(TRUE)->setAutowired(FALSE)->addTag('nette.presenter', $def->getClass());
			if (is_subclass_of($def->getClass(), 'Nette\Application\UI\Presenter')) {
				$def->addSetup('$invalidLinkMode', array(
					$this->debugMode ? UI\Presenter::INVALID_LINK_WARNING : UI\Presenter::INVALID_LINK_SILENT
				));
			}
		}
	}


	/** @return string[] */
	private function findPresenters()
	{
		$config = $this->getConfig();
		$classes = array();

		if ($config['scanDirs']) {
			$robot = new Nette\Loaders\RobotLoader;
			$robot->setCacheStorage(new Nette\Caching\Storages\DevNullStorage);
			$robot->addDirectory($config['scanDirs']);
			$robot->acceptFiles = '*' . $config['scanFilter'] . '*.php';
			$robot->rebuild();
			$classes = array_keys($robot->getIndexedClasses());
		}

		if ($config['scanComposer']) {
			$rc = new \ReflectionClass('Composer\Autoload\ClassLoader');
			$classFile = dirname($rc->getFileName()) . '/autoload_classmap.php';
			if (is_file($classFile)) {
				$this->getContainerBuilder()->addDependency($classFile);
				$classes = array_merge($classes, array_keys(call_user_func(function($path) {
					return require $path;
				}, $classFile)));
			}
		}

		$presenters = array();
		foreach (array_unique($classes) as $class) {
			if (strpos($class, $config['scanFilter']) !== FALSE && class_exists($class)
				&& ($rc = new \ReflectionClass($class)) && $rc->implementsInterface('Nette\Application\IPresenter')
				&& !$rc->isAbstract()
			) {
				$presenters[] = $rc->getName();
			}
		}
		return $presenters;
	}

}
